<?php

namespace App\Http\Controllers;

use App\Post;
use App\Share;
use Request as AjaxRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use File;

class ShareController extends Controller
{

    public function updateInfoShare(Request $request)
    {

        $id = AjaxRequest::input('id');

        $share = Share::findOrFail($id);

        return $share->infoStatusShare();
    }

    public function share(Request $request)
    {
        $postId = $request->input('id');

        $post = Post::findOrFail($postId);

        Share::create([
            'user_id' => $post->user_id,
            'user_id_share' => Auth::id(),
            'post_id' => $post->id
        ]);
        return response()->json(['success' => true]);
    }

    public function shareSharePost(Request $request)
    {
            $shareId = $request->input('id_share');

            $share = Share::findOrFail($shareId);

            Share::create([
                'user_id' => $share->user_id,
                'user_id_share' => Auth::id(),
                'post_id' => $share->post_id
            ]);
            return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $share = Share::findOrFail($id);

        if ($share->user_id_share == Auth::user()->id) {
            $share->likesShare()->delete();
            $share->delete();
        }

        return redirect()->back();
    }
}
