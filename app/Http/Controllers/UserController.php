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
use Input;
use Session;
use Carbon;
use Redirect;
use Auth;

class UserController extends Controller
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

       // dd("user controller");

      $users =  User::all();


      return view('users',compact('users'));
  }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

       // dd("user controller EDIT ",$id);

       $users =  User::where('id','=',$id)->get();

     // fetch users associated farmbooks
       $user_farmbooks =  $users->first()->farmbooks()->get();
      // array of farmbook id's for defaults in select in view
       $user_farmbooks = array_pluck($user_farmbooks, 'id');

      // get all farmbooks
       $farmbooks = Farmbook::lists('name','id');


//dd($users ,$user_farmbooks,$farmbooks );
       return view('edituser',compact('users','farmbooks','user_farmbooks'));
   }

    /**
     * List users farmbooks
     *
     * @return \Illuminate\Http\Response
     */
    public function listFarmbooks()

    {
        $id = Auth::user()->farmbooks()->get();
       // dd($id);



      $default = Auth::user()->farmbook;

       $farmbooks = $id->lists('name','id');


//dd($users ,$user_farmbooks,$farmbooks );
       return view('changefarmbook',compact('farmbooks','default'));

   }



 public function setFarmbook(Request $request)
 {

 $now = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();

  $farmbooks = $request->input('getfarmbook');

//dd($farmbooks[0]);

 // dd(Auth::user()->farmbook);




$id = Auth::user()->id;
$currentuser = User::find($id);


$currentuser->farmbook = $farmbooks[0];
$currentuser->save();
     Session::flash('flash_message', 'Farmbook changed. '.$now);
     Session::flash('flash_type', 'alert-success');
     return Redirect::back();
 //User::where('id', $id)->update(array('admin' => $admin,'active' => $active, 'farmbook' => $default, 'updated_at' => $now));

 }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request,$id)
    {


     // current timestamp
     $now = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();

     // get inpute
     $id = $request->input('id');
     $farmbooks = $request->input('getfarmbook');
     $admin = $request->input('admin');
     $active = $request->input('active');




      // set default to first
     $default = $farmbooks[0];

      // dd($admin,$active);
       // get the user
     $user =  User::where('id','=',$id)->first();


       //update farmbooks
     $user->farmbooks()->sync($farmbooks);

     User::where('id', $id)->update(array('admin' => $admin,'active' => $active, 'farmbook' => $default, 'updated_at' => $now));



      //  dd("user controller Store ",$id,,$farmbooks);
     Session::flash('flash_message', 'Updated '  .  $user->name  . ' at '.$now);
     Session::flash('flash_type', 'alert-success');
     return Redirect::back();
 }


}
