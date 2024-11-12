<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\LikeShare;
use App\Notification;
use App\Share;

class LikeShareController extends Controller
{
    public function LikeShare(Request $request)
    {
        if ($request->ajax()) {
            $shareId = $request->input('id');
            $share = Share::findOrFail($shareId);
            $this->handleLikeShare('App\Share', $shareId);

            if ($like = LikeShare::whereShareableType('App\Share')->whereShareableId($shareId)->whereUserId(Auth::id())->first()) {
                if ($share->user_id_share !== Auth::user()->id) {
                    if (!Notification::where('user_id', $share->user_id_share)->where('from', Auth::user()->id)->where('notification_type', 'App\LikeShare')->where('seen', 0)->first()) {
                        $like->notifications()->create([
                            'user_id' => $share->user_id_share,
                            'from' => Auth::user()->id
                        ]);
                    }
                }
                return 1;
            } else {
                Notification::where('user_id', $share->user_id)->where('from', Auth::user()->id)->where('notification_type', 'App\LikeShare')->delete();
                return 0;
            }
        }
    }

    public function handleLikeShare($type, $id)
    {
        $existingLike = LikeShare::whereShareableType($type)->whereShareableId($id)->whereUserId(Auth::id())->first();

        if (is_null($existingLike)) {
            $like = LikeShare::create([
                'user_id' => Auth::id(),
                'shareable_id' => $id,
                'shareable_type' => $type,
            ]);

            return $like;
        } else {
            LikeShare::whereShareableType($type)->whereShareableId($id)->whereUserId(Auth::id())->delete();
            Notification::where('from', Auth::id())
                ->where('notification_type', 'App\LikeShare')
                ->delete();
        }
    }
}