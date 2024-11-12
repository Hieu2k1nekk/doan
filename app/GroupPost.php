<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupPost extends Model
{
    protected $table = 'group_post';
    protected $fillable = ['group_id', 'post_id','approved'];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
