
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
			if (contactData.type == 'friend' && !contactData.approved) {
				newData.push(contactData);
			}
		});
		return newData;
	};
}]);

