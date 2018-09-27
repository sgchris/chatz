<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $fillable = [
		'message', 'user_id'
	];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

	public function scopeRecent($query)
	{
		return $query->orderBy('created_at', 'desc');
	}

	// dateTime: "Y-m-d H:i:s"
	public function scopeSince($query, $dateTime) 
	{
		return $query->where('created_at', '>', $dateTime);
	}
}
