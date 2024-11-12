<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\CommentsSharesController;

Route::get('/', [
    'uses' => 'HomeController@index',
    'as' => 'index'
]);


//Auth::routes();

Route::group(['middleware' => 'web'], function () {

    Route::post('login/', [
        'uses' => 'LoginController@login',
        'as' => 'login.attempt'
    ]);

    Route::post('register/', [
        'uses' => 'LoginController@register',
        'as' => 'register.attempt'
    ]);
});

Route::group(['middleware' => 'auth'], function () {

    Route::get('/test', function () {
        $user = App\User::find(1);
        return $user->friends()->sortByDesc('messages');
    });

    Route::get('/logout', [
        'uses' => 'LoginController@logout',
        'as' => 'logout'
    ]);

    Route::resource('/posts', 'PostsController', [
        'only' => [
            'store', 'update', 'destroy'
        ]
    ]);

    Route::delete('/share/{id}', [ShareController::class, 'destroy']);



    Route::resource('/comments', 'CommentsController', ['only' => [
        'store', 'destroy'
    ]]);

    Route::resource('/comments-shares', 'CommentsSharesController', ['only' => [
        'store'
    ]]);

    Route::delete('comments-shares/{id}', [CommentsSharesController::class, 'destroy'])->name('comments-shares.destroy');

    Route::resource('/events', 'EventsController', ['only' => [
        'index', 'create', 'store', 'destroy', 'show'
    ]]);

    Route::get('/profile/{id}', [
        'as' => 'profile.view',
        'uses' => 'ProfileController@view'
    ]);

    Route::get('/profile/{id}/edit', [
        'as' => 'profile.edit',
        'uses' => 'ProfileController@edit'
    ]);

    Route::post('/profile/update/{id}', [ProfileController::class, 'update_info'])->name('profile.update_info');

    Route::post('/profile/cover/update', [
        'uses' => 'ProfileController@changeCover',
        'as' => 'profile.changeCover'
    ]);

    Route::post('/profile/update', [
        'uses' => 'ProfileController@changeProfile',
        'as' => 'profile.changeProfile'
    ]);

    Route::get('/tag/{name}', [
        'as' => 'tag.search',
        'uses' => 'TagsController@search'
    ]);

    Route::get('/photos/{id}', [
        'as' => 'photos',
        'uses' => 'HomeController@photos'
    ]);

    Route::get('/friends/{id}', [
        'as' => 'friends',
        'uses' => 'HomeController@friends'
    ]);

    Route::get('/saved/', [
        'as' => 'saved',
        'uses' => 'HomeController@saved'
    ]);

    Route::get('/messages/', [
        'as' => 'messages',
        'uses' => 'MessageController@index'
    ]);

    Route::post('/online', [
        'as' => 'online',
        'uses' => 'HomeController@updateOnline'
    ]);

    Route::post('post/like', [
        'as' => 'post.like',
        'uses' => 'LikesController@LikePost'
    ]);

    Route::post('share/like', [
        'as' => 'share.like',
        'uses' => 'LikeShareController@LikeShare'
    ]);

    Route::post('post/info', [
        'as' => 'post.info',
        'uses' => 'PostsController@updateInfo'
    ]);
    Route::post('share/info', [
        'as' => 'share.info',
        'uses' => 'ShareController@updateInfoShare'
    ]);

    Route::post('search/friends', [
        'as' => 'search.post',
        'uses' => 'SearchController@search'
    ]);

    Route::post('friend/add', [
        'as' => 'friend.add',
        'uses' => 'FriendsController@SendRequest'
    ]);

    Route::post('friend/cancel', [
        'as' => 'friend.cancel',
        'uses' => 'FriendsController@CancelRequest'
    ]);

    Route::post('friend/remove', [
        'as' => 'friend.remove',
        'uses' => 'FriendsController@RemoveFriend'
    ]);

    Route::post('friend/accept', [
        'as' => 'friend.accept',
        'uses' => 'FriendsController@AcceptFriend'
    ]);

    Route::post('save/post', [
        'as' => 'save.post',
        'uses' => 'SaveController@save'
    ]);

    Route::post('share/post', [
        'as' => 'share.post',
        'uses' => 'ShareController@share'
    ]);

    Route::post('share/sharepost', [
        'as' => 'share.sharepost',
        'uses' => 'ShareController@shareSharePost'
    ]);


    Route::post('message/friend', [
        'as' => 'message.start',
        'uses' => 'MessageController@startChat'
    ]);

    Route::post('message/send', [
        'as' => 'message.send',
        'uses' => 'MessageController@sendMsg'
    ]);

    Route::post('notifications/seen', [
        'as' => 'notifications.seen',
        'uses' => 'HomeController@notifications'
    ]);
    Route::post('/profile/change-password/{id}', [ProfileController::class, 'changePassword'])->name('profile.changePassword');
});

use App\Http\Controllers\GroupController;

Route::middleware(['auth'])->group(function () {

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{id}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');

    // Quản lý nhóm
    Route::get('/groups/{id}/manage', [GroupController::class, 'manageGroup'])->name('groups.manage');
    Route::post('/groups/{groupId}/members/{memberId}/approve', [GroupController::class, 'approveMember'])->name('groups.approveMember');
    Route::delete('/groups/{groupId}/members/{memberId}/remove', [GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::post('/groups/{groupId}/posts/{postId}/approve', [GroupController::class, 'approvePost'])->name('groups.approvePost');
    Route::delete('/groups/{groupId}/posts/{postId}', [GroupController::class, 'deletePost'])->name('groups.deletePost');
    // Định nghĩa route cho groups.loadApprovedMembers
    Route::get('/groups/{id}/load-approved-members', [GroupController::class, 'showApprovedMembers'])->name('groups.loadApprovedMembers');

    // Định nghĩa route cho groups.loadApprovedPosts
    Route::get('/groups/{id}/load-approved-posts', [GroupController::class, 'showApprovedPosts'])->name('groups.loadApprovedPosts');
    // Tham gia nhóm
    Route::post('/groups/{groupId}/join', [GroupController::class, 'joinGroup'])->name('groups.join');
    Route::post('/groups/{groupId}/add-friend', [GroupController::class, 'addFriendToGroup'])->name('groups.addFriend');
    Route::delete('/groups/{groupId}/cancelJoinRequest', 'GroupController@cancelJoinRequest')->name('groups.cancelJoinRequest');
    Route::delete('/groups/{groupId}/leave', 'GroupController@leaveGroup')->name('groups.leave');

    Route::post('/groups/{groupId}/posts', 'GroupController@storeGroupPost')->name('groups.storePost');
    Route::post('/posts/{id}', 'PostsController@update')->name('posts.update');
});
