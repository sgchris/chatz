<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$validator = \Validator::make($request->all(), [
			'filter' => 'min:0|max:200', 
		]);
		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		// get the current user
		$user = $request->user();

		$filter = request('filter');

		// list of friends Ids
		$friendsIds = [];
		if (!empty($filter)) {
			$friends = $user->friends()->where(function($query) use ($filter) {
				$query->where('name', 'like', '%'.$filter.'%')
					->orWhere('email', 'like', '%'.$filter.'%');
			})->get();
			
			$friendsIds = $friends->pluck('id');

			$followers = $user->followers()->where(function($query) use ($filter) {
				$query->where('name', 'like', '%'.$filter.'%')
					->orWhere('email', 'like', '%'.$filter.'%');
			})->get();

			$records = $friends->merge($followers);
		} else {
			$friendsIds = $user->friends->pluck('id');

			// friends and followers
			$records = $user->friends->merge($user->followers);
		}

		// follows and friends may appear on both lists
		$records = $records->unique('id');

		// get only the relevant fields
		$users = [];
		foreach ($records as $user) {
			if ($user->id == $request->user()->id) {
				continue;
			}

			// friend or follower
			$isFriend = in_array($user->id, $friendsIds->toArray());

			$users[] = [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'joined' => $user->created_at->diffForHumans(),
				'type' => ($isFriend ? 'friend' : 'follower'),
				'approved' => $user->pivot->approved,
			];
		}

        return $users;
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
		$params = $request->all();
		$validator = \Validator::make($params, [
			'email' => 'required|email', 

			'name' => 'min:2|max:250',
			'password' => 'min:2|max:250',
			'is_registered' => 'numeric',
		]);
		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		$user = User::where('email', $params['email'])->get()->first();
		if ($user) {
			if (!$user->email_verified_at) {
				$this->_sendVerificationEmailToUser($user);
			}
		} else {

			$user = new User();
			$user->email = $params['email'];

			if (isset($params['name'])) {
				$user->name = $params['name'];
			}

			if (isset($params['password'])) {
				$user->password = bcrypt($params['password']);
			}

			if (isset($params['is_registered'])) {
				$user->is_registered = $params['is_registered'];
			}

			$user->save();
		}
		
		return ['result' => 'success', 'user' => $user];
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
		$params = $request->all();
		$validator = \Validator::make($params, [
			'name' => 'min:2|max:250',
			'email' => 'email', 
			'password' => 'min:2|max:250',
			'is_registered' => 'numeric',
			'approve_follower_id' => 'numeric|exists:users,id',
		]);

		if ($validator->fails()) {
			return ['error' => $validator->errors()];
		}

		if (isset($params['approve_follower_id'])) {
			$this->_approveFollowerId($request->user()->id, $params['approve_follower_id']);
		}

		// get the user object, and update relevant fields
		$user = $request->user();
		if (isset($params['name'])) {
			$user->name = $params['name'];
		}

		if (isset($params['email'])) {
			$user->email = $params['email'];
		}

		if (isset($params['password'])) {
			$user->password = bcrypt($params['password']);
		}

		if (isset($params['is_registered'])) {
			$user->is_registered = $params['is_registered'];
		}

		$user->save();

		return ['results' => 'success'];
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

	
	/**
	 * approve new relations
	 *
	 * @param int $userId the current user ID
	 * @param int $followerId the requesting user ID (the follower)
	 *
	 * @return bool
	 */
	protected function _approveFollowerId($userId, $followerId) 
	{
		return DB::table('relations')
			->where('user_id', $followerId)
			->where('friend_id', $userId)
			->update([
				'approved' => 1
			]);
	}


	/**
	 * send invitation email to a user
	 *
	 * @param User $user the user whom to send the email
	 *
	 * @return bool
	 */
	protected function _sendVerificationEmailToUser(User $user) 
	{
		// check if the email has to be sent (didn't send, or sent long time ago)
		$oneMonth = 60*60 * 24 * 31;
		$emailSent = false;
		if (!$user->email_verified_at) {
			// send verification email


			// send email result
			$emailSent = true;
		} elseif ($user->email_verified_at->timestamp < time() - $oneMonth) {
			// send verification reminder email


			// send email result
			$emailSent = true;
		}

		return $emailSent;
	}
}
