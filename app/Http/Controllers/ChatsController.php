<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Chat;
use App\User;
use App\ChatUser;
use Validator;
use Carbon\Carbon;

class ChatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
	{
        // get messages of a user
		$params = $request->only(['chat_ids']);
		$validator = \Validator::make($params, [
			// optional. format: 123,432,5345,234
			'chat_ids' => 'min:1|max:250|regex:/^[\d\,]+$/', 
		]);
		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		if (isset($params['chat_ids']) && !empty($params['chat_ids'])) {
			$chats = $request->user()->chats;
			$chats = $chats->whereIn('id', explode(',', $params['chat_ids']));
		} else {
			$chats = $request->user()->chats->sortByDesc('updated_at');
		}

		$res = [];
		foreach ($chats as $chat) {
			// for every chat, add author info
			$obj = $chat->only(['id', 'name']);
			$obj['users'] = [];
			foreach($chat->users as $user) {
				$obj['users'][] = [
					'id' => $user->id,
					'name' => $user->name,
					'email' => $user->email,
				];
			}

			// check unseen messages:
			// find the pivot table instance
			$chatUserInstance = DB::table('chat_user')
				->where('chat_id', $chat->id)
				->where('user_id', $request->user()->id)
				->get()
				->first();
			$lastMessage = $chat->messages()->recent()->get()->first();
			$lastVisitTS = Carbon::createFromFormat('Y-m-d H:i:s', $chatUserInstance->last_visit)->timestamp;
			$obj['newMessages'] = (
				$lastMessage && 
				$lastMessage->created_at->timestamp > $lastVisitTS
			);
			$res[] = $obj;
		}

		return $res;
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
			'name' => 'min:2|max:250|unique:chats', // optional
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
				// update "updated at"
				$chat->updated_at = \Carbon\Carbon::now();
				$chat->save();

				return ['error' => 'chat with that name already exists', 'chat_id' => $chat->id];
			}
		} else {
			// generate chat name
			$name = $request->user()->name . ' and ' . $attachedUser->name;
		}

		// check if the chat already exists
		foreach ($request->user()->chats as $chat) {
			// check if this is the chat
			if (count($chat->users) == 2 && 
				$chat->users->contains($userId) && 
				$userId != $request->user()->id
			) {
				// update "updated at"
				$chat->updated_at = \Carbon\Carbon::now();
				$chat->save();

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
    public function show(Request $request, Chat $chat)
    {
		if (!in_array($request->user()->id, $chat->users()->pluck('id')->all())) {
			return ['error' => 'no permissions'];
		}

		$obj = [
			'id' => $chat->id,
			'name' => $chat->name,
			'messages' => [],
		];

		$lastMessageTS = 0;

		// find the pivot table instance
		$chatUserInstance = DB::table('chat_user')
			->where('chat_id', $chat->id)
			->where('user_id', $request->user()->id)
			->get()
			->first();

		// get last visit time
		$lastVisitTS = $chatUserInstance && $chatUserInstance->last_visit ? 
			Carbon::createFromFormat('Y-m-d H:i:s', $chatUserInstance->last_visit)->timestamp : 0;

		// build the list with user's data
		foreach ($chat->messages->sortBy('created_at') as $message) {
			$messageObj = [
				'id' => $message->id,
				'message' => $message->message,
				'created_at' => $message->created_at->diffForHumans(),
				'user_id' => $message->user->id,
				'user_name' => $message->user->name,
				'user_email' => $message->user->email,
			];
			$obj['messages'][] = $messageObj;

			if ($message->created_at->timestamp > $lastMessageTS) {
				$lastMessageTS = $message->created_at->timestamp;
			}
		}

		// update chat "updated_at" (that's the "last seen" attr)
		if ($lastMessageTS > $lastVisitTS && $chatUserInstance) { 
			$chatUserInstance = DB::table('chat_user')
				->where('chat_id', $chat->id)
				->where('user_id', $request->user()->id)
				->update([
					'last_visit' => Carbon::now()
				]);
		}

		return $obj;
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
