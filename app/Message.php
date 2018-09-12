<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $fillable = [
		'message', 'user_id'
	];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

	public function scopeRecent($query)
	{
		return $query->orderBy('created_at', 'desc');
	}
}
