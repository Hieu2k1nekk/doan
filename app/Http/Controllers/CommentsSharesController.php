<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CommentsShares;
use App\Share;
use Auth;
use App\Notification;

class CommentsSharesController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'share_id' => 'required|integer|exists:shares,id',
            'body' => 'required|min:1|max:255'
        ]);

        // Tiếp tục thực hiện các bước khác nếu validation thành công
        $share = Share::findOrFail($request->input('share_id'));
        // Kiểm tra xem user có tồn tại không
        if (!$share->user) {
            // Xử lý trường hợp share không tồn tại user
            return redirect()->back()->with('error', 'Không tìm thấy thông tin người dùng của bài share.');
        }

        $comment = CommentsShares::create([
            'share_id' => $request->input('share_id'),
            'body' => $request->input('body'),
            'user_id' => Auth::user()->id
        ]);


        if ($share->user_id_share !== Auth::user()->id) {
            $comment->notifications()->create([
                'user_id' => $share->user_id_share,
                'from' => $share->user_id
            ]);
        }

        return redirect()->back()->with('success', 'Bình luận đã được đăng thành công!');
    }
    public function destroy($id)
    {
        $comment = CommentsShares::findOrFail($id);
        $share = Share::findOrFail($comment->share->id);


        if ($comment->user_id == Auth::user()->id || $comment->share->user_id_share == Auth::user()->id) {
            $comment->delete();
            Notification::where('user_id', $share->user_id_share)->where('from', Auth::user()->id)->where('notification_type', 'App\CommentsShares')->delete();
        }

        return redirect()->back();
    }
}
