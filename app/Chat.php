<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = ['name'];

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
		if ($this->users->contains($user->id)) {
			return false;
		}

		return $this->users()->attach($user->id);
	}
}
