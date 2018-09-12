<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Message;

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

	public function addMessage($messageText) 
	{
		$newMessage = new Message;
		$newMessage->message = $messageText;
		$newMessage->user_id = request()->user()->id;

		return $this->messages()->save($newMessage);
	}

	public function addUser(User $user) 
	{
		if ($this->users->contains($user->id)) {
			return false;
		}

		return $this->users()->attach($user->id);
	}
}
