<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Response;
use App\User;
Use Auth;

class LoginController extends Controller
{
    public function login(Request $request){

        $rules = array('email' => 'required|email|max:50', 'password' => 'required|min:8|max:20');
		$messages = array(
			'email.required' => 'Vui lòng nhập địa chỉ email.',
			'password.required' => 'Vui lòng nhập mật khẩu.',
			'password.min' => 'Mật khẩu tối thiểu :min ký tự.'
		);
		$validator = Validator::make($request->all(), $rules, $messages);

		// Validate the input and return correct response
		if ($validator->fails()){
		    return Response::json(array(
		        'success' => false,
		        'errors' => $validator->getMessageBag()->toArray()
		    ), 400); // 400 being the HTTP code for an invalid request.
		}

		if (Auth::attempt(['email' => request()->email, 'password' => request()->password])) {

			if ($request->has('remember')){
				$remember = true;
			}

            return Response::json(array('success' => true), 200);
        } else {
        	return Response::json(array(
		        'success' => false,
		        'errors' => array('credentials' => 'Tài khoản hoặc mật khẩu không chính xác.')
		    ), 400); // 400 being the HTTP code for an invalid request.
        }

    }

    public function register(Request $request){
    	$rules = array('name' => 'required|min:8|max:30', 'email' => 'required|email|max:50|unique:users', 'password' => 'required|min:8|max:20');
		$messages = array(
			// 'name.required' => 'Vui lòng nhập họ và tên.',
			// 'name.min' => 'Độ dài tối thiểu :min kí tự.',
			// 'email.required' => 'Vui lòng nhập địa chỉ email.',
			// 'email.unique' => 'Email đã được đăng ký.',
			// 'password.required' => 'Vui lòng nhập mật khẩu.',
			// 'password.min' => 'Mật khẩu tối thiểu :min kí tự.',
		);
		$validator = Validator::make($request->all(), $rules, $messages);

		$parts = explode(" ", $request->input('name'));
		$lastname = array_pop($parts);
		$firstname = implode(" ", $parts);

		if ($validator->fails()){
		    return Response::json(array(
		        'success' => false,
		        'errors' => $validator->getMessageBag()->toArray()
		    ), 400); 
		}

		if(empty(trim($firstname)) or empty(trim($lastname))){
			return Response::json(array(
		        'success' => false,
		        'errors' => [
		        	'name' => 'Phải có họ và tên.'
		        ]
		    ), 400); 
		}


		$user = new User;
		$user->first_name = $firstname;
		$user->last_name = $lastname;
		$user->email = $request->input('email');
		$user->password = bcrypt($request->input('password'));

		$user->save();

		Auth::login($user);

		return Response::json(array('success' => true), 200);

    }

    public function logout(){
    	Auth::logout();
    	return Redirect('/');
    }
}
