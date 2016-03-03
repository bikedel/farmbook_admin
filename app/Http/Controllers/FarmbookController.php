<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Property;
use App\Street;
use App\Complex;
use App\Owner;
use Carbon;
use App\Farmbook;
use Session;
use Redirect;
use DB;

class FarmbookController extends Controller
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

      $farmbooks =  Farmbook::orderBy('name')->get();


      return view('farmbooks',compact('farmbooks'));
  }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

       // dd("user controller EDIT ",$id);

       $farmbooks =  Farmbook::where('id','=',$id)->get();




//dd($users ,$user_farmbooks,$farmbooks );
       return view('editfarmbook',compact('farmbooks'));
   }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {

       // dd("user controller EDIT ",$id);


        $farmbooks =  Farmbook::find($id);


   //  dd($farmbooks->database,$id);
        $database = $farmbooks->database;

        $farmbooks->delete();



        $dbname = 'tmp';

         // connect to tmp database
        $otf = new \App\Database\OTF(['database' => $dbname]);
        $db = DB::connection($dbname);

        $sql = "DROP DATABASE ".$database;


  //set created to false
        $created = false;

        try {
    // delete database 
          $db->getpdo()->exec(  $sql);
          $created = true ;


      } catch (Exception $ex) {

    // dd( $ex->getMessage());
    // error creating database
          $message =  $ex->getMessage();
          Session::flash('flash_message', 'Error deleting '.$message);
          Session::flash('flash_type', 'alert-warning');
          return Redirect::back();
      }






      $now = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();


      Session::flash('flash_message', 'Farmbook deleted '  . ' at '.$now);
      Session::flash('flash_type', 'alert-success');
      return Redirect::back();
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
     $name = $request->input('name');
     $database = $request->input('database');
     $type = $request->input('type');


     Farmbook::where('id', $id)->update(array('name' => $name,'database' => $database, 'type' => $type, 'updated_at' => $now));



      //  dd("user controller Store ",$id,,$farmbooks);
     Session::flash('flash_message', 'Updated '  .  $name  . ' at '.$now);
     Session::flash('flash_type', 'alert-success');
     return Redirect::back();
 }


}
