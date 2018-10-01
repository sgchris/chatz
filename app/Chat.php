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

	public function addMessage($messageText, $user_id = null)
	{
		$newMessage = new Message;
		$newMessage->message = $messageText;

		// set the user of the message (the sender)
		if (is_null($user_id)) {
			$user_id = request()->user()->id;
		}

		$newMessage->user_id = $user_id;

		return $this->messages()->save($newMessage);
	}

	public function addUser(User $user) 
	{
		if ($this->users->contains($user->id)) {
			return false;
		}

		return $this->users()->attach($user->id);
	}

	public function latestMessage() 
	{
		return $this->messages->sortByDesc('created_at')->first();
	}
}
