
// the sidebar contact stripe with the data
app.directive('sidebarContact', [function() {
	var obj = {
		scope: {
			ngClick: '&?',
			contact: '=ngModel',
		},
		link: function(scope, element, attributes) {
			// check the click callback
			scope.ngClick = scope.ngClick || function(){};

			scope.type = scope.contact.type
		},
		template: '<div>' + 
			'<a href="" class="list-group-item list-group-item-action" ' + 
				'ng-click="ngClick();">type: {{contact.type}}' + 
				// waiting for response
				'<span class="contact-type" ng-show="contact.type == \'friend\' && !contact.approved" ' + 
					'title="{{contact.name}} (email {{contact.email}}) still did not respond to your friend request" '+
					'ng-class="{\'contact-pending\': contact.approved == 0}">Waiting for response</span>' + 
				// Pending frield request
				'<span class="contact-type" ng-show="contact.type == \'follower\' && !contact.approved" ' + 
					'title="{{contact.name}} (email {{contact.email}}) wants to add you to his contacts" '+
					'ng-class="{\'contact-pending\': contact.approved == 0}">Approve friend request</span>' + 
				'<h5 class="mb-1">{{ contact.name }}</h5>' + 
				'<p class="mb-1">{{ contact.email }}</p>' + 
				'<small>Joined {{ contact.joined }}</small>' + 
			'</a>' + 
		'</div>'
	};

	return obj;
}]);
