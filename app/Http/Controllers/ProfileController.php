<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use App\User;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function view($id)
    {

        $user = User::findOrFail($id);

        return view('app.profile')->with('user', $user);
    }

    public function edit($id)
    {

        $user = User::findOrFail($id);

        if ($user->id !== Auth::user()->id) {
            return redirect()->back();
        }

        return view('app.profile.edit')->with('user', $user);
    }

    public function changeCover(Request $request)
    {

        $rules = array(
            'image' => 'required|image'
        );
        

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(Auth::user()->getCoverPath(), $filename);

            $img = Image::make([asset(Auth::user()->getCoverPath() . $filename)]);
            //$img->fit(1300,400);
            $img->save([Auth::user()->getCoverPath() . $filename]);

            Auth::user()->update([
                'cover' => $filename
            ]);
        }

        return Response()->json(array(
            'success' => true
        ));
    }

    public function changeProfile(Request $request)
    {
        $rules = array(
            'image' => 'required|image'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return Response::json(array(
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ), 400);
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(Auth::user()->getAvatarPath(), $filename);

            $img = Image::make([asset(Auth::user()->getAvatarPath() . $filename)]);
            //$img->fit(500,500);
            $img->save([Auth::user()->getAvatarPath() . $filename]);

            Auth::user()->update([
                'avatar' => $filename
            ]);
        }

        return Response()->json(array(
            'success' => true
        ));
    }
    public function update_info(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'birthday' => [
                'date',
                'before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            ],
            'description' => 'nullable',
            'address' => 'nullable',
        ]);
        //        dd($request->all());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray(),
            ], 400);
        }

        $user->update($request->only(['first_name', 'last_name', 'birthday', 'description', 'address']));
        //        dd($validator->getMessageBag()->toArray());
        return redirect()->back();
    }
    public function showChangePasswordForm($id)
    {
        $user = User::findOrFail($id);

        if ($user->id !== Auth::user()->id) {
            return redirect()->back();
        }

        return view('app.profile.change-password')->with('user', $user);
    }


    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ], [
            // 'password.min' => 'Mật khẩu phải có ít nhất :min ký tự',
            // 'password.confirmed' => 'Mật khẩu không khớp!',
            // 'password.required' => 'Nhập!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray(),
            ]);
        }

        $user->update(['password' => bcrypt($request->password)]);

        return response()->json(['success' => true]);
    }
}
