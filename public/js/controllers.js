app.controller('HomeController', ['$scope', '$http', 'WebAPI', function($scope, $http, WebAPI) {

	$scope.ui = {
		tab: 'chats'
	};

	$scope.contacts = {
		data: [],
		load: function() {
		}
	}

	$scope.chats = {
		data: [],
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
