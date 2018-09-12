var app = angular.module('chatz', []);

// fix AJAX calls
app.config(['$httpProvider', function($httpProvider) {
	$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

	/**
	 * The workhorse; converts an object to x-www-form-urlencoded serialization.
	 * @param {Object} obj
	 * @return {String}
	 */
	var param = function(obj) {
		var query = '',
			name, value, fullSubName, subName, subValue, innerObj, i;

		for (name in obj) {
			value = obj[name];

			if (value instanceof Array) {
				for (i = 0; i < value.length; ++i) {
					subValue = value[i];
					fullSubName = name + '[' + i + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			} else if (value instanceof Object) {
				for (subName in value) {
					subValue = value[subName];
					fullSubName = name + '[' + subName + ']';
					innerObj = {};
					innerObj[fullSubName] = subValue;
					query += param(innerObj) + '&';
				}
			} else if (value !== undefined && value !== null) {
				query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
			}
		}

		return query.length ? query.substr(0, query.length - 1) : query;
	};

	// Override $http service's default transformRequest
	$httpProvider.defaults.transformRequest = [function(data) {
		return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
	}];
}]);

app.service('WebAPI', ['$http', function($http) {
	return function(params) {
		// add API token
		params.params = params.params || {};
		params.params.api_token = window.API_TOKEN;

		// add base URL to the beginning
		if (params.url) {
			if (params.url.charAt(0) != '/') {
				params.url = '/' + params.url;
			}

			params.url = window.BASE_URL + '/api' + params.url;
		}

		// perform the request
		return $http.apply(this, arguments);
	};
}]);


app.controller('HomeController', ['$scope', '$http', 'WebAPI', function($scope, $http, WebAPI) {

	$scope.ui = {
		tab: 'chats'
	};

	$scope.contacts = {
		load: function() {
		}
	}

	$scope.init = {
		apiToken: window.API_TOKEN,
		baseUrl: window.API_TOKEN,

		load: function() {
			WebAPI({
				method: 'get',
				url: 'chats'
			}).then(function(res) {
				console.log('res', res);
			});
		}
	}

	$scope.init.load();
}]);
