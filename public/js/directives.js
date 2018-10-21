
// the sidebar contact stripe with the data
app.directive('sidebarContact', ['WebAPI', function(WebAPI) {
	var obj = {
		scope: {
			ngClick: '&?',
			ngOnFollowerApprove: '&?',
			contact: '=ngModel',
		},
		link: function(scope, element, attributes) {
			// check the click callback
			scope.ngClick = scope.ngClick || function(){};

			scope.type = scope.contact.type;

			scope.approveFriendRequest = function() {
				if (!confirm('Approve '+scope.contact.name)) {
					return;
				}

				WebAPI({
					method: 'put', 
					url: 'users',
					data: {
						approve_follower_id: scope.cotnact.id
					}
				}).then(function(res) {
					// callback
					if (typeof(scope.ngOnFollowerApprove) == 'function') {
						scope.ngOnFollowerApprove();
					}

				}, function(res) {
					console.err('Approve follower - Server error', res);
				});
			};
		},
		template: '<div>' + 
			'<a href="" class="list-group-item list-group-item-action" ' + 
				'ng-click="ngClick();">type: {{contact.type}}' + 
				// waiting for response
				'<span class="contact-type" ng-show="contact.type == \'friend\' && !contact.approved && !contact.responded" ' + 
					'title="{{contact.name}} (email {{contact.email}}) still did not respond to your friend request" '+
					'ng-class="{\'contact-pending\': contact.approved == 0}">Waiting for response</span>' + 
				// Pending frield request
				'<span class="contact-type" ng-show="contact.type == \'follower\' && !contact.approved && !contact.responded" ' + 
					'title="{{contact.name}} (email {{contact.email}}) wants to contact with you" '+
					'ng-click="approveFriendRequest()" ' + 
					'ng-class="{\'contact-pending\': contact.approved == 0}">Approve friend request</span>' + 
				'<h5 class="mb-1">{{ contact.name }}</h5>' + 
				'<p class="mb-1">{{ contact.email }}</p>' + 
				'<small>Joined {{ contact.joined }}</small>' + 
			'</a>' + 
		'</div>'
	};

	return obj;
}]);
