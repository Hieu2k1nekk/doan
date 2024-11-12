<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    protected $table = 'shares';

    protected $fillable = [
        'user_id', 'user_id_share', 'post_id'
    ];

    public function post(){
        return $this->belongsTo('App\Post', 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id_share');
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'user_id_share');
    }
    public function likesShare()
    {
        return $this->morphMany('App\LikeShare', 'shareable');
    }
    public function commentsShares()
    {
        return $this->hasMany('App\CommentsShares', 'share_id');
    }
    public function infoStatusShare()
    {
       return $this->likesShare()->count().' Lượt thích | '. $this->commentsShares()->count(). ' Bình luận';
    }


}
