<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Property;
use App\Street;
use App\Complex;
use App\Owner;
use App\Farmbook;
use DB;
use Auth;

class HomeController extends Controller
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


        // get user id 
        // connect to farmbook and get default database


        $database = Auth::user()->getDatabase();


        //dd(  $userDB,$database);
        //$otf = new \App\Database\OTF(['database' => $database]);   

        //change database for Street
        $street = new Street;
        $street->changeConnection(    $database  );


        $streets = Street::on($database )->orderBy('strStreetName','ASC')->lists('strStreetName','id');




        $complexes = Complex::on($database )->orderBy('strComplexName','ASC')->lists('strComplexName', 'id');
        $owners = Owner::on($database )->orderBy('strIDNumber','ASC')->lists('strIDNumber', 'id');
        $properties = Property::on($database )->orderBy('strKey','ASC')->lists('strkey', 'id');



        return view('search',compact('streets','complexes','owners','properties'));
    }
}
