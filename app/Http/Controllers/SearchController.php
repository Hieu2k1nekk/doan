<?php

namespace App\Http\Controllers;

use Request;
use DB;
use App\User;
use Auth;

class SearchController extends Controller
{
    public function search(){
    	$q = Request::input('q');

    	if (strlen($q) < 1){
    		return 'Chuỗi tìm kiếm phải có ít nhất 1 kí tự.';
    	}
    	
    	$users = User::where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', '%'.$q.'%')->whereNotIn('id', [Auth::user()->id])->get();

    	return view('layouts.search_results')->with('users', $users)->with('q', $q);

    }
}
