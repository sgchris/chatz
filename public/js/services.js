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
		console.log('arguments', arguments);
		return $http.apply(this, arguments);
	};
}]);



