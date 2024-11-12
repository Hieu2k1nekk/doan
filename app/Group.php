<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['id', 'name', 'description', 'user_id'];

    public function getName()
    {
        return $this->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')->withTimestamps();
    }
    // Phương thức kiểm tra xem một người dùng có phải là admin của nhóm hay không
    public function isAdmin($user)
    {
        return $this->user_id === $user->id;
    }
    public function isMember($user)
    {
        return $this->users()->where('user_id', $user->id)->where('approved', true)->exists();
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('approved')
            ->withTimestamps();
    }
    public function approvedPosts()
    {
        return $this->belongsToMany(Post::class, 'group_post')->wherePivot('approved', true);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'group_post', 'group_id', 'post_id');
    }
}
