<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Property;
use App\Street;
use App\Complex;
use App\Owner;
use App\user;
use App\Farmbook;
use App\Farmbook_user;
use Auth;


class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


      $username = Auth::user()->name ;
      $user = User::where('name' , '=', $username)->first();

      $user_farmbooks =  $user->farmbooks()->get();
  

$user_farmbooks = array_pluck($user_farmbooks, 'id');

  // dd($user_farmbooks,$array);

      $farmbooks = Farmbook::lists('name','id');
    //  $p->farmbooks()->attach([1,2]);
    //  dd($user,$p);

 //$user_farmbooks = [1,2];



      return view('settings',compact('user','farmbooks','user_farmbooks'));
    }
}
