<?php

namespace App\Http\Controllers;

use App\Share;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Response;

use Request as AjaxRequest;
use App\Post;
use Auth;
use File;

class PostsController extends Controller
{

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|min:3|max:255',
//            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ], [
            // 'body.required' => 'Vui lòng nhập nội dung.',
            // 'body.min' => 'Bài viết trên 3 ký tự.',
            // 'image.mimes' => 'Phải là định dạng ảnh.',
            // 'image.max' => 'Kích cỡ ảnh nhỏ hơn 2048Kb.'
        ]);

        if ($validator->fails()) {
            return Response()->json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }

        $post = Post::create([
            'body' => $request->input('body'),
            'user_id' => Auth::user()->id
        ]);

        $tags = $this->getTags($request->input('body'));

        foreach ($tags as $tag) {
            $post->tags()->create([
                'name' => $tag
            ]);
        }

        if ($request->hasFile('image')) {
            $images = $request->file('image');

            foreach ($images as $image) {
                if ($image->isValid()) {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $image->move('img/posts/', $filename);

                    $post->images()->create(['filename' => $filename]);
                }
            }
        }

        return Response()->json(array(
            'success' => true,
        ));
    }

    // public function update(Request $request, $id)
    // {
    //     $post = Post::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'body' => 'required|min:3|max:255',
    //         'image' => 'image|mimes:jpeg,png,jpg|max:2048',
    //     ], [
    //         // 'body.required' => 'Vui lòng nhập nội dung.',
    //         // 'body.min' => 'Bài viết trên 3 ký tự.',
    //         // 'image.mimes' => 'Phải là định dạng ảnh có đuôi .jpeg, .png, .jpg.',
    //         // 'image.max' => 'Kích cỡ ảnh nhỏ hơn 2048Kb.'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->getMessageBag()->toArray()
    //         ]);
    //     }

    //     $post->body = $request->input('body');

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         if ($image->isValid()) {
    //             $filename = time() . '.' . $image->getClientOriginalExtension();
    //             $image->move('img/posts/', $filename);

    //             // Xóa ảnh cũ nếu nó tồn tại
    //             if ($post->images->count() > 0) {
    //                 File::delete(public_path('img/posts/' . $post->images[0]->filename));
    //                 $post->images()->delete();
    //             }

    //             $post->images()->create(['filename' => $filename]);
    //         }
    //     }

    //     $post->save();

    //     return response()->json(['success' => true]);
    // }
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
    
        $validator = Validator::make($request->all(), [
            'body' => 'required|min:3|max:255',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ]);
        }
    
        $post->body = $request->input('body');
    
        // Kiểm tra xem có tệp hình ảnh mới không
        if ($request->hasFile('images')) {
            $newImages = $request->file('images');

            foreach ($newImages as $newImage) {
                if ($newImage->isValid()) {
                    $filename = time() . '.' . $newImage->getClientOriginalExtension();
                    $newImage->move('img/posts/', $filename);

                    // Xóa ảnh cũ nếu nó tồn tại
                    if ($post->images->count() > 0) {
                        foreach ($post->images as $image) {
                            File::delete(public_path('img/posts/' . $image->filename));
                            $image->delete();
                        }
                    }

                    $post->images()->create(['filename' => $filename]);
                }
            }
        } else {
            // Nếu không có tệp hình mới, giữ nguyên tên file ảnh hiện tại
            if ($post->images->count() > 0) {
                $filename = $post->images[0]->filename;
                $post->images()->update(['filename' => $filename]);
            }
        }
    
        $post->save();
    
        return response()->json(['success' => true]);
    }
    

    public function destroy($id)
    {

        $post = Post::findOrFail($id);

        if ($post->user_id == Auth::user()->id) {
            $post->likes()->delete();
            $post->comments()->delete();
            $post->tags()->delete();
            $post->delete();
        }

        return redirect()->back();
    }
}
