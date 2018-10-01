<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Chat;
use App\Message;
use App\User;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // get messages of a user
		$params = $request->only(['since']);
		$validator = \Validator::make($params, [
			'since' => 'date_format:"Y-m-d H:i:s"', // might be empty or not supported
		]);

		// set the default when no 'since' provided
		if (!isset($params['since']) || empty($params['since'])) {
			$params['since'] = '1970-1-1 0:0:0';
		}

		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		$since = $params['since'];
		
		return $request->user()->latestMessages($since);
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
    public function store(Request $request, $chat_id)
    {
		$params = array_merge($request->all(), ['chat_id' => $chat_id]);
		$validator = \Validator::make($params, [
			'message' => 'required|min:0|max:65535', // might be empty
			'chat_id' => 'required|numeric', // might be empty
		]);
		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}
		
		// find the chat
		$chat = Chat::find($chat_id);
		if (!$chat) {
			return ['error' => 'chat not found'];
		}

		$messageText = $request->get('message');
		$chatId = $request->get('chat_id');

		if (!$request->user()->chats->contains($chat_id)) {
			return ['error' => 'no permissions to add messages to this chat'];
		}

		$newMessage = $chat->addMessage($messageText);
		
		return [
			'result' => 'success', 
			'message_id' => $newMessage->id
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
