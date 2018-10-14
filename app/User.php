<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    public function chats()
    {
        return $this->belongsToMany(Chat::class);
    }

	public function friends() 
	{
		return $this->belongsToMany(User::class, 'relations', 'user_id', 'friend_id')
			->withPivot('approved', 'email_sent_at');
	}

	public function followers() 
	{
		return $this->belongsToMany(User::class, 'relations', 'friend_id', 'user_id')
			->withPivot('approved', 'email_sent_at');
	}

	/**
	 * get latest messages from all the chats of the user
	 * (messages from other users too)
	 *
	 * @param string $since format "Y-m-d H:i:s"
	 * 
	 * @return array of App\Message instances
	 */
	public function latestMessages($since)
	{
		$messages = [];

		foreach ($this->chats as $chat) {
			$chatLatestMessages = Message::where('chat_id', $chat->id)
				->since($since)->get()->all();
			$messages = array_merge($messages, $chatLatestMessages);
		}

		return $messages;
	}

}
