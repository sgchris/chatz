var app = angular.module('chatz', []);

app.controller('HomeController', ['$scope', '$http', function($scope, $http) {

	$scope.contacts = {
		load: function() {
		}
	}
});
