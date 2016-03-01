<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Property;
use App\Street;
use App\Note;
use App\Owner;
use Redirect;
use Session;
use Auth;
use Carbon;
use App\User;
use URL;

class OwnerController extends Controller
{



    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {

        // set database
        $database = Auth::user()->getDatabase();




       //change database for Street
        $property = new Property;
        $property->changeConnection(    $database  );

        // get inputs
        $Input = $request->input('input');
  


//dd(   $streetInput,$streetSelect);
        // check if input or select
        // if input ignore select

        if (strlen($Input) > 0 ) {
          // search 
          $search = $Input;
          $properties = Property::on( $database)->where('strOwners', 'LIKE', "%$search%")->orderby('strOwners','ASC')->get();

      } else {

         Session::flash('flash_message', ''  . "Please input an Owner Name.");
         Session::flash('flash_type', 'alert-danger');
         return Redirect::back();


      }

     


      {
        Session::put('search',  $Input);
        Session::put('controllerroute',  '/owners');
      }

        // view properties
        // return with error if no result
      if ($properties->count()){
          return view('owners',compact('properties','search'));
      }
      else{
         Session::flash('flash_message', ''  . "No properties matching search criteria.");
         Session::flash('flash_type', 'alert-danger');
         return Redirect::back();
     }

 }


    // edit all
 public function rolledit($id)
 {
    try{

        // set database
        $database = Auth::user()->getDatabase();


       //change database
        $property = new Property;
        $property->changeConnection(    $database  );



        // search on street name
        $query = Property::on(   $database)->like('strOwners', $id)->orderby('strOwners','ASC')->get();
        $properties = Property::on(   $database )->like('strOwners', $id)->orderby('strOwners','ASC')->simplePaginate(1);

        // get relationship data
        $properties->load('owner', 'note');

        // get total records as simplepagination does not do this
        $count =  $query->count();
        $search = $id;

    }
    catch (exception $e)
    {
        dd($e->getMessage());
    }





    return view('property',compact('properties','count','search'));

}




}
