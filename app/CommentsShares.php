<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Post;
class CommentsShares extends Model
{
    protected $table = 'comments_shares';

    protected $fillable = [
        'user_id', 'share_id', 'body'
    ];

    public function canDelete($postId){
        $comment = CommentsShares::findOrFail($this->id);
        $share = Share::findOrFail($postId);

        if (Auth::user()->id == $share->user_id){
            // creator of the post, so can delete any comment on his own post!
            return true;
        } elseif (Auth::user()->id == $comment->user_id){
            // can delete own comment
            return true;
        } else {
            return false;
        }

    }

    public function share(){
        return $this->belongsTo('App\Share');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function notifications(){
        return $this->morphMany('App\Notification', 'notification');
    }

}
