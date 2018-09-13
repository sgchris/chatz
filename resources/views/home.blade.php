@extends('layouts.app')

@section('content')

<div class="container-fluid app-wrapper" ng-app="chatz" ng-controller="HomeController">
	<ng-include src="'angular-templates/home.html'"></ng-include>
</div>

<script>
window.API_TOKEN='{{ $api_token }}';
window.BASE_URL='{{ url('/') }}';
</script>

<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.7.2/angular.min.js"></script>

<script src="{{ url('js/init_app.js') }}"></script>
<script src="{{ url('js/services.js') }}"></script>
<script src="{{ url('js/controllers.js') }}"></script>

@endsection
