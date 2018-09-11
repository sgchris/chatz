@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-app="chatz" ng-controller="HomeController">
    <div class="row justify-content-center">
		<div class="col-12 col-sm-3">
            <div class="card">
                <div class="card-header">Chats</div>
                <div class="card-body">
                </div>
            </div>
		</div>
        <div class="col-sm-9">
            <div class="card">
                <div class="card-header">Message</div>
                <div class="card-body">
                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.7.2/angular.min.js"></script>
@endsection
