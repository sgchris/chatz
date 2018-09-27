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
		getNewMessages: function() {
			if ($scope.chats.pollTimer) {
				$timeout.cancel($scope.chats.pollTimer);
			}
			var promise = WebAPI({
				method: 'get',
				url: 'messages',
				params: {
					since: $scope.messages.lastMessageTime
				}
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

				// set the focus on the new message input
				if (setNewMessageFocus) {
					$timeout(function() {
						angular.element('#newMessageText').focus();
					});
				}

			}).finally(function() {
				$scope.chats.pollTimer = $timeout(function() {
					$scope.chats.show(chatId);
				}, 5000);
			});

			return promise;

		},
		show: function(chatId) {
			if ($scope.chats.pollTimer) {
				$timeout.cancel($scope.chats.pollTimer);
			}
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

				// set the focus on the new message input
				if (setNewMessageFocus) {
					$timeout(function() {
						angular.element('#newMessageText').focus();
					});
				}

			}).finally(function() {
				$scope.chats.pollTimer = $timeout(function() {
					$scope.chats.show(chatId);
				}, 5000);
			});

			return promise;
		},
		load: function() {
			var promise = WebAPI({
				method: 'get',
				url: 'chats'
			});
			
			promise.then(function(res) {
				$scope.chats.data = res.data;

				if ($scope.chats.data.length > 0) {
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
}]);
