app.controller('HomeController', ['$scope', '$http', '$timeout', 'WebAPI', 'TabFocus', 'BrowserNotification',
	function($scope, $http, $timeout, WebAPI, TabFocus, BrowserNotification) {

	$scope.ui = {
		tab: 'chats',
	};

	$scope.contacts = {
		data: [],
		filter: '',

		_timer: null,
		_timerDelay: 1000,

		displayNewEmailAddress: false,
		newEmailAddress: '',
		create: function() {
			// check if the user exists (if there's something in contacts panel)
			if ($scope.contacts.data.length > 0) {
				// open a chat with the first user
				return $scope.chats.create($scope.contacts.data[0].id);
			}

			// create new user
			WebAPI({
				method: 'post',
				url: 'users',
				data: {
					email: $scope.contacts.newEmailAddress
				}
			}).then(function(res) {
				if (res.data.result == 'success') {
					// open a chat with the user
					$scope.chats.create(res.data.user.id);

					// reload the contacts
					$scope.contacts.load();

					return;
				}
				
				alert('Cannot create new user');
				console.error('Cannot create new user', res);
			});
		},

		openForm: function() {
			$scope.contacts.displayNewEmailAddress = true; 
			$scope.ui.tab = 'contacts'; 

			// set the new email address with what typed in the filter
			$scope.contacts.newEmailAddress = $scope.contacts.filter;

			// focus on the form
			$scope.contacts.focusNewEmailAddress();
		},

		closeForm: function() {
			$scope.contacts.displayNewEmailAddress = false;
			// move the typed email to the filter
			$scope.contacts.filter = $scope.contacts.newEmailAddress;
		},

		focusNewEmailAddress: function() {
			// focus the new email address input
			$timeout(function() {
				angular.element('input#newEmailAddress').get(0).focus();
			});
		},

		load: function(delayed) {
			// cancel the previous timer
			if ($scope.contacts._timer) {
				$timeout.cancel($scope.contacts._timer);
			}

			if (delayed) {
				$scope.contacts._timer = $timeout(function() {
					$scope.contacts.load();
				}, $scope.contacts._timerDelay);
				return;
			}

			var filterString = $scope.contacts.displayNewEmailAddress ? 
				$scope.contacts.newEmailAddress : 
				$scope.contacts.filter;

			var params = {
				filter: filterString
			};

			WebAPI({
				method: 'get',
				url: 'users',
				params: params
			}).then(function(res) {
				console.log('received web api', res);
				$scope.contacts.data = res.data;
			});
		}
	}

	$scope.chats = {
		selectedChat: null,

		data: [],

		pollTimer: null,

		// create new chat
		create: function(contactId) {
			WebAPI({
				method: 'post',
				url: 'chats',
				data: {
					user_id: contactId
				}
			}).then(function(res) {
				// move to the chats tab
				$scope.ui.tab = 'chats';

				$scope.chats.load().then(function() {
					// get chat information
					$scope.chats.show(res.data.chat_id);
				});
			});

		},

		scrollConversationToTheBottom: function() {
			// scroll down to the bottom
			angular.element('.conversation-wrapper').get(0).scrollTo(
				0, angular.element('.conversation-wrapper .messages-list').get(0).clientHeight
			);
		},

		lastMessageTime: null,

		newMessagesTimer: null,
		getNewMessages: function() {
			if ($scope.chats.newMessagesTimer) {
				$timeout.cancel($scope.chats.newMessagesTimer);
			}

			// set parameters
			var params = {};
			if ($scope.chats.lastMessageTime) {
				params['since'] = $scope.chats.lastMessageTime;
			}

			var promise = WebAPI({
				method: 'get',
				url: 'messages',
				params: params
			});
			
			promise.then(function(res) {
				if (res.data.error) {
					return false;
				}

				var newMessages = res.data,
					updatedChats = [];

				newMessages.forEach(function(messageData) {

					// update 'last message time'
					if (!$scope.chats.lastMessageTime) {
						$scope.chats.lastMessageTime = messageData.created_at;
					} else if (
						(new Date($scope.chats.lastMessageTime)) < 
						(new Date(messageData.created_at))
					) {
						$scope.chats.lastMessageTime = messageData.created_at;
					}

					// collect the updated chat IDs
					if (updatedChats.indexOf(messageData.chat_id) < 0) {
						updatedChats.push(messageData.chat_id);
					}
				});

				if (updatedChats.length > 0) {
					$scope.chats.load(updatedChats).then(function(res) {
						if (res.data.error) {
							return false;
						}

						res.data.forEach(function(updatedChatData) {
							// update the current chat record
							var updatedExistingChat = false;
							$scope.chats.data.forEach(function(existingChatData, i) {
								if (existingChatData.id == updatedChatData.id) {
									$scope.chats.data[i] = updatedChatData;

									// load the messages if this is the currently selected chat
									if (existingChatData.id == $scope.chats.selectedChat.id) {
										$scope.chats.show(existingChatData.id);
									}

									updatedExistingChat = true;
								}
							});

							// prepend new chat
							if (!updatedExistingChat) {
								$scope.chats.data.unshift(updatedChatData);
							}
						});
					});

					// trigger notification
					if (!document.hasFocus()) {
						BrowserNotification.notify('New message(s) in '+updatedChats.length+' chat(s)', function() {
							// show the first unread message
							if (updatedChats.indexOf($scope.chats.selectedChat.id) < 0) {
								$scope.chats.show(updatedChats[0]);
							}
						});
					}
				}

			}).finally(function() { 
				$scope.chats.newMessagesTimer = $timeout(function() {
					$scope.chats.getNewMessages();
				}, 5000);
			});

			return promise;

		},
		show: function(chatId) {
			var promise = WebAPI({
				method: 'get',
				url: 'chats/' + chatId
			});
			
			promise.then(function(res) {
				if (res.data.error) {
					return false;
				}

				var setNewMessageFocus = (
					!$scope.chats.selectedChat ||
					$scope.chats.selectedChat.id != res.data.id
				);

				$scope.chats.selectedChat = res.data;

				// mark as "seen"
				$scope.chats.data.forEach(function(chatData, i) {
					if (chatData.id == $scope.chats.selectedChat.id) {
						$scope.chats.data[i].newMessages = false;
					}
				});

				// set the focus on the new message input
				if (setNewMessageFocus) {
					$timeout(function() {
						$scope.chats.scrollConversationToTheBottom();

						angular.element('#newMessageText').focus();
						angular.element('#newMessageText').get(0).focus();
					});
				}

				// scroll to the bottom
				$timeout(function() {
					$scope.chats.scrollConversationToTheBottom();
				});
			});

			return promise;
		},

		calculateLatestMessageTime: function() {
			$scope.chats.data.forEach(function(chatData) {
				var chatLatestMessage = new Date(chatData.latest_message.created_at);
				var lastMessageTime = new Date($scope.chats.lastMessageTime);

				if (chatLatestMessage > lastMessageTime) {
					$scope.chats.lastMessageTime = chatLatestMessage.toMySqlString();
				}
			});
		},

		// load list of chats
		load: function(chatIds) {
			var chatIdsProvided = (chatIds && chatIds.length > 0);

			var params = {};
			if (chatIdsProvided) {
				params['chat_ids'] = chatIds.join(',');
			}
			var promise = WebAPI({
				method: 'get',
				url: 'chats',
				params: params
			});
			
			promise.then(function(res) {
				if (!chatIdsProvided) {
					$scope.chats.data = res.data;
				}

				// show the first chat (only if loading ALL the available chats)
				if (!chatIdsProvided && $scope.chats.data.length > 0) {
					// calculate the latest message time
					$scope.chats.calculateLatestMessageTime();

					// show the first chat
					$scope.chats.show($scope.chats.data[0].id);
				}
			});

			return promise;
		}
	};

	$scope.messages = {
		newMessageText: '',
		newMessageSending: false,

		notifyNewMessages: false,

		create: function() {
			// avoid sending twice
			if ($scope.messages.newMessageSending) {
				return false;
			}

			$scope.messages.newMessageSending = true;

			WebAPI({
				method: 'post',
				url: 'chats/' + $scope.chats.selectedChat.id + '/messages',
				data: {
					message: $scope.messages.newMessageText
				}
			}).then(function(res) {
				// reload the chat
				$scope.chats.show($scope.chats.selectedChat.id).then(function() {
					$scope.messages.newMessageText = '';
				});
			}).finally(function() {
				$scope.messages.newMessageSending = false;
			});

		}
	};


	$scope.init = {
		load: function() {
		}
	}

	$scope.contacts.load();
	$scope.chats.load();

	$timeout(function() {
		$scope.chats.getNewMessages();
	}, 5000);

}]);
