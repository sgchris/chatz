<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chat;
use App\User;
use Validator;

class ChatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$validator = \Validator::make($request->all(), [
			'name' => 'min:2|max:250',
			'user_id' => 'required|numeric',
		]);
		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		$name = $request->get('name');
		$userId = $request->get('user_id');

		$attachedUser = User::find($userId);
		if (!$attachedUser) {
			return ['error' => 'User does not exist'];
		}

		// check the name
		if (!empty($name)) {
			$chat = Chat::where('name', 'like', $name)->get();
			if ($chat->count() != 0) {
				return ['error' => 'chat with that name already exists'];
			}
		} else {
			// generate chat name
			$name = $request->user()->name . ' and ' . $attachedUser->name;
		}

		// check if the chat already exists
		//dd($request->user()->chats);
		foreach ($request->user()->chats as $chat) {
			//dd(count($chat->users), $chat->users->contains($userId), $userId, $request->user()->id);
			// check if this is the chat
			if (count($chat->users) == 2 && 
				$chat->users->contains($userId) && 
				$userId != $request->user()->id
			) {
				return [
					'error' => 'chat already exists', 
					'chat_id' => $chat->id
				];
			}
		}

		// create the chat and add the two users (current and attached)
		$newChat = Chat::create([
			'name' => $name
		]);
		$newChat->addUser($request->user());
		$newChat->addUser($attachedUser);

		return [
			'result' => 'success', 
			'chat_id' => $newChat->id
		];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
