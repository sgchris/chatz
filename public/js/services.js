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

	var obj = {
		isOnFocus: true,
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

	// set callbacks
	angular.element(window).on('focus', function() {
		obj.isOnFocus = true;

		focusCallbacks.forEach(function(focusCallback) {
			focusCallback();
		});
	});
	angular.element(window).on('blur', function() {
		obj.isOnFocus = false;

		blurCallbacks.forEach(function(blurCallback) {
			blurCallback();
		});
	});

	return obj;
}]);



app.service('BrowserNotification', ['$timeout', function($timeout) {
	// flag to avoid checking the support several times
	var checkedSupport = false;

	// the return object
	var obj = {
		// "onClickCallbackFn" doesn't work
		createNotification: function(title, message, onClickCallbackFn) {
			var notification = new Notification(title, {
				body: message
			});

			if (typeof(callbackFn) == 'function') {
				notification.onclick = onClickCallbackFn;
			}
		},
		_hasBrowserSupport: function() {
			// Let's check if the browser supports notifications
			if (!("Notification" in window)) {
				if (!checkedSupport) {
					console.warn("This browser does not support desktop notification");
					checkedSupport = true;
				}

				return false;
			}

			return true;
		},
		notify: function(msg, onClickCallbackFn) {
			if (!obj._hasBrowserSupport()) {
				return false;
			}

			// Let's check whether notification permissions have already been granted
			if (window.Notification.permission === "granted") {
				// If it's okay let's create a notification
				obj.createNotification('Chatz', msg, onClickCallbackFn);

			} else if (window.Notification.permission !== "denied") {
				// Otherwise, we need to ask the user for permission
				window.Notification.requestPermission(function (permission) {
					// If the user accepts, call the function recursively
					if (permission === "granted") {
						obj.notify(msg, onClickCallbackFn);
					}
				});
			}

			return false;
		}
	};

	return obj;

}]);
