<?php

namespace App\Http\Controllers;

use App\GroupUser;
use App\Group;
use App\User;
use App\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::all();
        return view('app.group.index', compact('groups'))->with('active', 'groups');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:6',
            'description' => 'nullable|string|max:1000',
        ], [
            // 'name.min' => 'Tên nhóm phải có ít nhất :min ký tự.',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::user()->id,
        ]);
        $group->members()->attach(Auth::user()->id, ['approved' => true]);

        return redirect()->route('groups.index');
    }

    public function edit($id)
    {
        $group = Group::findOrFail($id);

        if (Auth::user()->id !== $group->user_id) {
            return redirect()->route('groups.index')->with('error', 'Bạn không có quyền chỉnh sửa nhóm này.');
        }

        return view('app.group.edit', compact('group'))->with('active', 'groups');
    }

    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        if (Auth::user()->id !== $group->user_id) {
            return redirect()->route('groups.index')->with('error', 'Bạn không có quyền chỉnh sửa nhóm này.');
        }

        $request->validate([
            'name' => 'required|string|max:255|min:6',
            'description' => 'nullable|string|max:1000',
        ], [
            // 'name.min' => 'Tên nhóm phải có ít nhất :min ký tự.',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('groups.index')->with('success', 'Nhóm đã được cập nhật thành công.');
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        if (Auth::user()->id !== $group->user_id) {
            return redirect()->route('groups.index')->with('error', 'Bạn không có quyền xóa nhóm này.');
        }

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Nhóm đã được xóa thành công.');
    }

    public function manageGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        if (Auth::user()->id !== $group->user_id) {
            return redirect()->route('groups.index');
        }

        // Lấy danh sách thành viên đã được chấp nhận
        $approvedMembers = $group->members()->wherePivot('approved', true)->get();
        $unapprovedMembers = $group->members()->wherePivot('approved', false)->get();
        $unapprovedPosts = $group->posts()->where('approved', false)->get();
        $group = Group::findOrFail($groupId);
        $approvedPosts = $group->approvedPosts;
        $user = User::find(Auth::user()->id);

        return view('app.group.manage', compact('group', 'approvedMembers', 'unapprovedMembers', 'unapprovedPosts', 'approvedPosts', 'user'))->with('active', 'groups');
    }


    public function addFriendToGroup(Request $request, $groupId)
    {
        // Validate request
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        // Find the group
        $group = Group::findOrFail($groupId);

        // Kiểm tra xem người dùng được xác thực có phải là chủ sở hữu của nhóm không
        if (Auth::user()->id !== $group->user_id) {
            return redirect()->route('groups.index')->with('error', 'You are not authorized to perform this action.');
        }
        // Kiểm tra xem người bạn đó đã là thành viên của nhóm chưa
        if ($group->members()->where('user_id', $request->friend_id)->exists()) {
            return redirect()->route('groups.manage', $groupId)->with('error', 'Người dùng này đã là thành viên của nhóm.');
        }
        // Thêm bạn bè vào nhóm
        $group->members()->attach($request->friend_id);

        return redirect()->route('groups.manage', $groupId)->with('success', 'Friend added to the group successfully.');
    }

    public function approveMember($groupId, $memberId)
    {
        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($memberId);

        // Kiểm tra xem người dùng là thành viên chưa được chấp nhận trong nhóm hay không
        if ($group->users()->where('user_id', $user->id)->where('approved', false)->exists()) {
            $group->users()->updateExistingPivot($user->id, ['approved' => true]);

            return redirect()->route('groups.manage', $group->id)->with('success', 'Yêu cầu tham gia của thành viên đã được chấp nhận.');
        }

        return redirect()->route('groups.manage', $group->id)->with('error', 'Yêu cầu tham gia không hợp lệ.');
    }


    public function removeMember($groupId, $memberId)
    {
        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($memberId);

        // Xóa tất cả bài viết của người dùng khỏi nhóm
        $userPosts = $group->posts()->where('user_id', $user->id)->get();
        foreach ($userPosts as $post) {
            // Xóa hình ảnh liên quan đến bài viết nếu có
            foreach ($post->images as $image) {
                // Xóa file hình ảnh từ thư mục
                if (file_exists(public_path('img/posts/' . $image->filename))) {
                    unlink(public_path('img/posts/' . $image->filename));
                }
                // Xóa hình ảnh từ cơ sở dữ liệu
                $image->delete();
            }

            // Xóa bài viết từ cơ sở dữ liệu
            $post->delete();
        }

        // Xóa người dùng khỏi nhóm và bảng group_user
        $group->users()->detach($user->id);

        return redirect()->route('groups.manage', $group->id);
    }


    public function approvePost($groupId, $postId)
    {
        $group = Group::findOrFail($groupId);
        $post = Post::findOrFail($postId);
        if (!$post->groupPost->approved) {
            // Cập nhật trạng thái approved của bài viết trong bảng group_post sang true
            $post->groupPost()->update(['approved' => true]);

            return redirect()->route('groups.manage', $post->groupPost->group_id)->with('success', 'Bài viết đã được chấp nhận và hiển thị trong nhóm.');
        }
    }

    public function deletePost($groupId, $postId)
    {
        $post = Post::findOrFail($postId);
        $post->delete();

        return redirect()->route('groups.manage', $groupId);
    }

    public function joinGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Kiểm tra xem người dùng đã là thành viên của nhóm hay không
        if ($group->isMember(Auth::user())) {
            return redirect()->route('groups.index')->with('success', 'Bạn đã là thành viên của nhóm này.');
        }

        // Kiểm tra xem người dùng đã gửi yêu cầu tham gia nhóm trước đó chưa
        if ($group->users()->where('user_id', Auth::user()->id)->where('approved', false)->exists()) {
            return redirect()->route('groups.index')->with('success', 'Yêu cầu của bạn đã được gửi. Vui lòng đợi xác nhận từ admin.');
        }

        // Thêm yêu cầu vào bảng group_user với trạng thái chưa được chấp nhận
        $group->users()->attach(Auth::user()->id, ['approved' => false]);

        return redirect()->route('groups.index')->with('success', 'Yêu cầu của bạn đã được gửi. Vui lòng đợi xác nhận từ admin.');
    }

    public function cancelJoinRequest($groupId)
    {
        $userId = Auth::user()->id;

        GroupUser::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('approved', false)
            ->delete();

        return redirect()->route('groups.index')->with('success', 'Yêu cầu tham gia đã được hủy.');
    }

    public function leaveGroup($groupId)
    {
        $groupUser = GroupUser::where('group_id', $groupId)
            ->where('user_id', Auth::user()->id)
            ->where('approved', true) // Chỉ xử lý những thành viên đã được chấp nhận
            ->delete();

        return redirect()->route('groups.index')->with('error', 'Không tìm thấy thông tin thành viên để rời nhóm.');
    }

    public function create()
    {
        return view('app.group.create')->with('active', 'groups');
    }

    public function show($id)
    {
        // Lấy thông tin chi tiết của nhóm với $id và truyền nó đến view
        $group = Group::findOrFail($id);
        return view('app.group.show', compact('group'))->with('active', 'groups');
    }

    public function updateInfo()
    {

        $id = AjaxRequest::input('id');

        $post = Post::findOrFail($id);

        return $post->infoStatus();
    }

    public function getTags($body)
    {

        preg_match_all('/#(\w+)/', $body, $matches);

        $tags = array();

        for ($i = 0; $i < count($matches[1]); $i++) {

            $tag = $matches[1][$i];

            array_push($tags, $tag);
        }

        return $tags;
    }

    public function storeGroupPost(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        // Kiểm tra xem người dùng có quyền đăng bài trong nhóm không
        if (!$group->isMember(Auth::user())) {
            return response()->json(['error' => 'Bạn không có quyền đăng bài trong nhóm này.'], 403);
        }

        $rules = array(
            'body' => 'required|min:3|max:255',
            'image' => 'image'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }

        // Tạo bài viết mới trong nhóm
        $post = new Post([
            'body' => $request->input('body'),
            'user_id' => Auth::user()->id,
            'approved' => false, // Đặt trạng thái approved là false

        ]);

        $group->posts()->save($post); // Lưu bài viết vào nhóm

        // Trích xuất các thẻ từ nội dung bài viết và lưu chúng vào cơ sở dữ liệu
        $tags = $this->getTags($request->input('body'));
        foreach ($tags as $tag) {
            $post->tags()->create([
                'name' => $tag
            ]);
        }

        // Xử lý hình ảnh nếu được tải lên
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            if ($image->isValid()) {
                $filename = time() . '.' . $image->getClientOriginalExtension();
                $image->move('img/posts/', $filename);

                // Lưu thông tin về hình ảnh vào cơ sở dữ liệu
                $post->images()->create(['filename' => $filename]);
            }
        }
        return redirect()->route('groups.show', $groupId)->with('success', 'Bài viết của bạn đã được chia sẻ thành công.');
    }

    public function showApprovedMembers($id)
    {
        $group = Group::findOrFail($id);
        $approvedMembers = $group->approvedMembers; // Lấy danh sách thành viên đã được xác nhận từ model
        $unapprovedMembers = $group->unapprovedMembers; // Lấy danh sách thành viên chưa được xác nhận từ model

        // Trả về view và truyền dữ liệu vào template
        return view('app.group.tabs.approvedMembers', compact('approvedMembers', 'unapprovedMembers'));
    }

    public function showApprovedPosts($id)
    {
        $group = Group::findOrFail($id);
        $approvedPosts = $group->approvedPosts(); // Thay thế này với logic lấy dữ liệu từ database
        return view('app.group.tabs.approvedPosts', compact('approvedPosts'));
    }
}
