app.controller('HomeController', ['$scope', '$http', '$timeout', 'WebAPI', function($scope, $http, $timeout, WebAPI) {

	$scope.ui = {
		tab: 'chats',
		contactsFilter: '',
	};

	$scope.contacts = {
		data: [],

		_timer: null,
		_timerDelay: 1000,

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

			WebAPI({
				method: 'get',
				url: 'users',
				params: {
					filter: $scope.ui.contactsFilter
				}
			}).then(function(res) {
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
				}
				return;


				var newMessages = res.data;
				var missingChats = [];
				if (!newMessages || newMessages.length === 0) {
					return true;
				}
				var existingChatIds = $scope.chats.data.map(function(chatData) {
					return chatData.id;
				});

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

					// check if this is a new chat (not in the list)
					if (
						existingChatIds.indexOf(messageData.chat_id) < 0 &&
						missingChats.indexOf(messageData.chat_id) < 0
					) {
						missingChats.push(messageData.chat_id);
					}
					
					// mark the chat as 'has unread messages'
					$scope.chats.data.forEach(function(chatData, i) {
						if (chatData.id == messageData.chat_id) {
							$scope.chats.data[i].newMessages = true;
						}
					});
				});

				if (missingChats.length > 0) {
					$scope.chats.load(missingChats).then(function(res) {
						if (res.data.error) {
							return false;
						}
						$scope.chats.data = $scope.chats.data.concat(res.data);
					});
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

				// check if we need to set focus on the new message input
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
						angular.element('#newMessageText').focus();
					});
				}

			});

			return promise;
		},

		calculateLatestMessageTime: function() {

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
