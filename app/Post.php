<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Image;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'body', 'user_id'
    ];

    protected $post_image_path = 'img/posts/';

    public function infoStatus()
    {
        return $this->likes()->count() . ' Lượt thích | ' . $this->comments()->count() . ' Bình luận | '. $this->share()->count() . ' Chia sẻ';
    }


    public function imagePath(Image $img)
    {
        return $this->post_image_path . $img->filename;
    }

    /* Relations */

    public function notifications()
    {
        return $this->morphMany('App\notification', 'notification');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_post', 'post_id', 'group_id');
    }
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    
    public function inGroup()
    {
        return GroupPost::where('post_id', $this->id)->where('approved', true)->exists();
    }
    

    public function groupPost()
    {
        return $this->hasOne(GroupPost::class, 'post_id');
    }
    public function likes()
    {
        return $this->morphMany('App\Like', 'likeable');
    }
    public function likesShare()
    {
        return $this->morphMany('App\LikeShare', 'shareable');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function commentsShares()
    {
        return $this->hasMany('App\CommentsShares');
    }

    public function saves()
    {
        return $this->hasMany('App\Save');
    }
    public function share()
    {
        return $this->hasMany('App\Share');
    }

    public function tags()
    {
        return $this->hasMany('App\Tag');
    }

    public function images()
    {
        return $this->morphMany('App\Image', 'imageable');
    }
    public function inGroupWithoutFriendship($user)
    {
        return $this->group()->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();
    }
    public function getApprovedGroupsAttribute()
    {
        return $this->groups()->wherePivot('approved', true)->get();
    }
    public function approvedPosts()
    {
        return $this->belongsToMany(Post::class, 'group_post')
            ->withPivot('approved')
            ->wherePivot('approved', true);
    }
}
