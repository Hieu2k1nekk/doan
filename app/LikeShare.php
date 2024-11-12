<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LikeShare extends Model
{
	protected $fillable = [
		'user_id', 'shareable_id', 'shareable_type'
	];

    public function shareable(){
    	return $this->morphTo();
    }

    public function user(){
    	return $this->belongsTo('App\User');
    }

    public function notifications(){
		return $this->morphMany('App\Notification', 'notification');
	}
}

