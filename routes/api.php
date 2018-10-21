<?php

use Illuminate\Http\Request;

use App\User;
use App\Chat;
use App\Message;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function() {

	//
	// GET 
	//

	Route::get('/user', function (Request $request) {
		return $request->user();
	});

	Route::get('/users', 'UsersController@index');

	// optional: chat_ids: 23,43,213,... (max 250 chats)
	Route::get('/chats', 'ChatsController@index'); 

	Route::get('/chats/{chat}', 'ChatsController@show');

	Route::get('/chats/{chat}/messages', function (Request $request, Chat $chat) {
		if ($request->user() != $chat->user()) {
			return ['error' => 'no permissions'];
		}

		return $chat->messages()->recent()->limit(30)->get();
	});

	// get all messages
	// params: 
	// 	since=2018-09-25 14:51:43, (mysql proper datetime format "Y-m-d H:i:s")
	// 	...
	Route::get('/messages', 'MessagesController@index');


	// 
	// PUT 
	// 

	// 'name', 'email', 'password', 'is_registered', 'approve_follower_id'
	Route::put('/users', 'UsersController@update'); 

	//
	// POST
	//

	Route::post('/users', 'UsersController@store'); // *email, name, password, is_registered

	Route::post('/chats', 'ChatsController@store'); // name (optional), user_id

	Route::post('/chats/{chat_id}/messages', 'MessagesController@store'); // message

});

