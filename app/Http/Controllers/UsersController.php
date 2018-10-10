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
		if (!empty($filter)) {
			$friends = $user->friends->where('name', 'like', '%'.$filter.'%')
				->orWhere('email', 'like', '%'.$filter.'%')->get();

			$followers = $user->followers->where('name', 'like', '%'.$filter.'%')
				->orWhere('email', 'like', '%'.$filter.'%')->get();

			$records = $friends->merge($followers)->all()
		} else {
			$records = User::all();
		}
		
		// get only the relevant fields
		$users = [];
		foreach ($records as $user) {
			if ($user->id == $request->user()->id) {
				continue;
			}

			$users[] = [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'joined' => $user->created_at->diffForHumans(),
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
