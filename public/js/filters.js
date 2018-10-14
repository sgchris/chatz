
// convert HTML to trusted content
app.filter('to_trusted', ['$sce', function($sce){
	return function(text) {
		return $sce.trustAsHtml(text);
	};
}]);



// convert HTML to trusted content
app.filter('onlyPendingContacts', ['$sce', function($sce){
	return function(data) {
		var newData = [];
		data.forEach(function(contactData) {
			console.log('contactData', contactData);
		});
		return data;
	};
}]);

