<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //
    public function messages() 
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

	public function addUser(User $user) 
	{
		if ($this->users->contains($user)) {
			return false;
		}

		return $this->users->add($user);
	}
}
