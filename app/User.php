<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use DB;

use Auth;
use App\Post;
use App\Image;
use App\Comment;
use App\Share;

class User extends Authenticatable
{
    use Notifiable;

    protected $userImagePath = 'img/users/';

    protected $userCoverPath = 'img/users/cover/';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'birthday', 'email', 'password', 'cover', 'avatar', 'description', 'address'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot'
    ];

    public function getAvatarImagePath()
    {
        return asset($this->userImagePath . $this->avatar);
    }

    public function getCoverImagePath()
    {
        return asset($this->userCoverPath . $this->cover);
    }

    public function getCoverPath()
    {
        return $this->userCoverPath;
    }

    public function getAvatarPath()
    {
        return $this->userImagePath;
    }

    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function likedPost($id)
    {
        if ($this->likes()->whereLikeableId($id)->whereLikeableType('App\Post')->whereUserId(Auth::id())->first()) {
            return true;
        } else {
            return false;
        }
    }

    public function likedShare($id)
    {
        if ($this->likesShares()->whereShareableId($id)->whereShareableType('App\Share')->whereUserId(Auth::id())->first()) {
            return true;
        } else {
            return false;
        }
    }

    public function savedPost($id)
    {
        if ($this->saves()->wherePostId($id)->whereUserId(Auth::user()->id)->first()) {
            return true;
        } else {
            return false;
        }
    }

    public function sharedPost($id)
    {
        return $this->share()->wherePostId($id)->exists();
    }

    public function getTimeline()
    {
        // Truy vấn bài đăng từ bảng Post
        $posts = Post::where('user_id', Auth::user()->id)
            ->orWhereIn('user_id', Auth::user()->friends()->pluck('id'))
            ->select('id', 'user_id', 'body', 'created_at', 'updated_at')
            ->get();

        // Truy vấn bài chia sẻ từ bảng Share
        $shares = Share::with('post')
            ->where(function ($query) {
                $query->where('user_id_share', Auth::user()->id)
                    ->orWhereHas('post', function ($query) {
                        $query->where('user_id', Auth::user()->id);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Kết hợp kết quả từ cả hai bảng vào một mảng duy nhất
        $timeline = $posts->concat($shares);

        // Sắp xếp mảng duy nhất theo thời gian
        $sortedTimeline = $timeline->sortByDesc('created_at');

        return $sortedTimeline;
    }



    public function getImages()
    {
        return Image::where('imageable_type', 'App\Post')->whereIn('imageable_id', [11])->get();
    }

    public function imagesFromPosts()
    {
        return $this->posts()->whereHas('images', function ($query) {
            $query->where('imageable_type', 'App\Post');
        })->get();
    }

    public function updateOnlineStatus()
    {
        $now = Carbon::now();

        if ($this->online()->count()) {
            // update
            $this->online()->update([
                'last' => $now
            ]);
        } else {
            // create
            $this->online()->create([
                'last' => $now
            ]);
        }
    }

    public function isOnline()
    {
        $now = Carbon::now()->subMinutes(2);
        if ($online = $this->online()->whereUserId($this->id)->first()) {
            if ($online->last > $now) {
                // online
                return true;
            } else {
                // offline
                return false;
            }
        }
    }

    public function PendingMessages($friend)
    {
        return Message::where('read', 0)->where('user_id', $friend->id)->where('receiver', $this->id)->count();
    }

    public function friendsLastActivity()
    {
        $friendList = $this->friends()->pluck('id');

        // posts, comments, Likes,

        $posts = Post::whereIn('user_id', $friendList)->orderBy('created_at', 'desc')->take(3)->get();
        $comments = Comment::whereIn('user_id', $friendList)->orderBy('created_at', 'desc')->take(3)->get();
        $likes = Like::whereIn('user_id', $friendList)->orderBy('created_at', 'desc')->take(3)->get();
        $shares = Share::whereIn('user_id_share', $friendList)->orderBy('created_at', 'desc')->take(3)->get();
        $likesShare = LikeShare::whereIn('user_id', $friendList)->orderBy('created_at', 'desc')->take(3)->get();
        $commentsShares = CommentsShares::whereIn('user_id', $friendList)->orderBy('created_at', 'desc')->take(3)->get();

        $activity = "";

        foreach ($posts as $post) {
            if ($post->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $post->user->id]) . '">' . $post->user->getFullName() . '</a> đã tạo bài viết mới ' . $post->created_at->diffForHumans() . '.<br>';
            }
        }

        foreach ($comments as $comment) {
            if ($comment->user && $comment->post && $comment->post->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $comment->user->id]) . '">' . $comment->user->getFullName() . "</a> đã bình luận lúc " . $comment->created_at->diffForHumans() . " vào bài viết của " . '<a href="' . route('profile.view', ['id' => $comment->post->user->id]) . '">' . $comment->post->user->getFullName() . "</a>.<br>";
            }
        }

        foreach ($likes as $like) {
            if ($like->user && $like->likeable && $like->likeable->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $like->user->id]) . '">' . $like->user->getFullName() . '</a> đã thích bài viết của <a href="' . route('profile.view', ['id' => $like->likeable->user->id]) . '">' . $like->likeable->user->getFullName() . "</a>.<br>";
            }
        }

        foreach ($shares as $share) {
            if ($share->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $share->user_id_share]) . '">' . $share->getUser->getFullName() . '</a> đã chia sẻ ' . $share->created_at->diffForHumans() . '.<br>';
            }
        }

        foreach ($likesShare as $likeShare) {
            if ($likeShare->user && $likeShare->shareable && $likeShare->shareable->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $likeShare->user->id]) . '">' . $likeShare->user->getFullName() . '</a> đã thích bài chia sẻ của <a href="' . route('profile.view', ['id' => $likeShare->shareable->getUser->id]) . '">' . $likeShare->shareable->getUser->getFullName() . "</a>.<br>";
            }
        }

        foreach ($commentsShares as $commentSharess) {
            if ($commentSharess->user && $commentSharess->share && $commentSharess->share->user) {
                $activity .= '<a href="' . route('profile.view', ['id' => $commentSharess->user->id]) . '">' . $commentSharess->user->getFullName() . "</a> đã bình luận lúc " . $commentSharess->created_at->diffForHumans() . " vào bài chia sẻ của " . '<a href="' . route('profile.view', ['id' => $commentSharess->share->getUser->id]) . '">' . $commentSharess->share->getUser->getFullName() . "</a>.<br>";
            }
        }

        return $activity;
    }


    /* Relations */

    public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

    public function online()
    {
        return $this->hasOne('App\Online');
    }

    public function messages()
    {
        return $this->hasMany('App\Message');
    }

    public function messagesInverse()
    {
        return $this->hasMany('App\Message', 'receiver');
    }

    public function conversation($friend)
    {
        $this->messagesInverse()->where('user_id', $friend->id)->update([
            'read' => true
        ]);
        $collect1 = $this->messages()->where('receiver', $friend->id)->get();
        $collect2 = $this->messagesInverse()->where('user_id', $friend->id)->get();
        $conversation = $collect1->merge($collect2)->sortBy('id');;
        return $conversation;
    }

    public function events()
    {
        return $this->hasMany('App\Event');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function likes()
    {
        return $this->hasMany('App\Like');
    }

    public function likesShares()
    {
        return $this->hasMany('App\LikeShare');
    }

    public function saves()
    {
        return $this->hasMany('App\Save');
    }

    public function share()
    {
        return $this->hasMany('App\Share');
    }

    public function shares()
    {
        return $this->hasMany('App\Share', 'user_id_share');
    }

    public function friendsOfMine()
    {
        return $this->belongsToMany('App\User', 'friends', 'user_id', 'friend_id');
    }

    public function friendOf()
    {
        return $this->belongsToMany('App\User', 'friends', 'friend_id', 'user_id');
    }

    public function friends()
    {
        return $this->friendsOfMine()->wherePivot('accepted', true)->get()
            ->merge($this->friendOf()->wherePivot('accepted', true)->get());
    }

    public function friendRequests()
    {
        return $this->friendsOfMine()->wherePivot('accepted', false)->get();
    }

    public function friendRequestsPending()
    {
        return $this->friendOf()->wherePivot('accepted', false)->get();
    }

    public function hasFriendRequestPending(User $user)
    {
        return (bool) $this->friendRequestsPending()->where('id', $user->id)->count();
    }

    public function hasFriendRequestPendingFrom(User $user)
    {
        return (bool) $this->friendRequests()->where('id', $user->id)->count();
    }

    public function HasAnyFriendRequestsPending()
    {
        return $this->friendsOfMine()->wherePivot('user_id', Auth::user()->id)->where('accepted', 0)->get();
    }

    public function addFriend(User $user)
    {
        return $this->friendOf()->attach($user->id);
    }

    public function removeFriend(User $user)
    {
        return $this->friendOf()->detach($user->id);
    }

    public function acceptFriend(User $user)
    {
        return (bool) $this->friendRequests()->where('id', $user->id)->first()->pivot->update([
            'accepted' => true
        ]);
    }

    public function isFriendsWith(User $user)
    {
        return (bool) $this->friends()->where('id', $user->id)->count();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot('approved')
            ->withTimestamps();
    }
    // Trong mô hình User
    public function userInGroups()
    {
        // Kiểm tra xem người dùng thuộc nhóm nào không
        return GroupUser::where('user_id', $this->id)->where('approved', true)->exists();

    }
}
