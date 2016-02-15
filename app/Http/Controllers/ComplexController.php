<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Property;
use App\Street;
use App\Note;
use App\Owner;
use App\Complex;
use Redirect;
use Session;
use Auth;
use Carbon;
use App\User;
use URL;

class ComplexController extends Controller
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
        $street = new Complex;
        $street->changeConnection(    $database  );

       //change database for Street
        $property = new Property;
        $property->changeConnection(    $database  );

        // get inputs
        $Input = $request->input('input');
        $Select = $request->input('selected');


//dd(   $Input,$Select,$request);
        // check if input or select
        // if input ignore select

        if (strlen($Input) > 0 ) {
          // search 
          $search = $Input;
          $properties = Property::on( $database)->like('strComplexName', $search)->orderby('strComplexName','ASC')->orderby('strStreetName','ASC')->orderby('numComplexNo','ASC')->get();

      } else {
          // search 
          $complex = Complex::on( $database)->where('id', $Select)->first();
          $search = $complex->strComplexName;
          $properties = Property::on( $database)->where('strComplexName', $search)->orderby('strComplexName','ASC')->orderby('strStreetName','ASC')->orderby('numComplexNo','ASC')->get();

      }

     


      {
        Session::put('search',  $Select);
        Session::put('controllerroute',  '/complex');
      }

        // view properties
        // return with error if no result
      if ($properties->count()){
          return view('complexes',compact('properties','search'));
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
        $query = Property::on(   $database)->like('strComplexName', $id)->orderby('strComplexName','ASC')->orderby('strStreetName','ASC')->orderby('numComplexNo','ASC')->get();
        $properties = Property::on(   $database )->like('strComplexName', $id)->orderby('strComplexName','ASC')->orderby('strStreetName','ASC')->orderby('numComplexNo','ASC')->simplePaginate(1);

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

//dd($properties);


    return view('property',compact('properties','count','search'));

}




}
