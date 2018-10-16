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
        //
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
