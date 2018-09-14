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

	Route::get('/chats', 'ChatsController@index');

	Route::get('/chats/{chat}', 'ChatsController@show');

	Route::get('/chats/{chat}/messages', function (Request $request, Chat $chat) {
		if ($request->user() != $chat->user()) {
			return ['error' => 'no permissions'];
		}

		return $chat->messages()->recent()->limit(30)->get();
	});

	//
	// POST
	//

	Route::post('/chats', 'ChatsController@store'); // name (optional), user_id

	Route::post('/chats/{chat_id}/messages', 'MessagesController@store'); // message

});

