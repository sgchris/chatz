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


// check when the tab is active
app.service('TabFocus', [function() {
	var focusCallbacks = [],
		blurCallbacks = [];

	// set callbacks
	angular.element(window).on('focus', function() {
		focusCallbacks.forEach(function(focusCallback) {
			focusCallback();
		});
	});
	angular.element(window).on('blur', function() {
		blurCallbacks.forEach(function(blurCallback) {
			blurCallback();
		});
	});

	return {
		onFocus: function(callbackFn) {
			if (typeof(callbackFn) == 'function') {
				focusCallbacks.push(callbackFn);
			}
		},

		onBlur: function(callbackFn) {
			if (typeof(callbackFn) == 'function') {
				blurCallbacks.push(callbackFn);
			}
		}
	};
}]);
