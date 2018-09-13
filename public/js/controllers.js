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

				// get chat information
				$scope.chats.show(res.data.chat_id);
			});

		},
		show: function(chatId) {
			WebAPI({
				method: 'get',
				url: 'chats/' + chatId
			}).then(function(res) {
				if (res.data.error) {
					return false;
				}

				$scope.chats.selectedChat = res.data;
			});
		},
		load: function() {
			WebAPI({
				method: 'get',
				url: 'chats'
			}).then(function(res) {
				$scope.chats.data = res.data;
			});
		}
	}


	$scope.init = {
		load: function() {
		}
	}

	$scope.contacts.load();
	$scope.chats.load();
}]);
