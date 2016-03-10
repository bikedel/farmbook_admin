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
use App\Note;

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


        //change database dynamically to user set database
        $street = new Street;
        $street->changeConnection(    $database  );


        $streets = Street::on($database )->orderBy('strStreetName','ASC')->lists('strStreetName','id');
        $complexes = Complex::on($database )->orderBy('strComplexName','ASC')->lists('strComplexName', 'id');
       // $owners = Owner::on($database )->orderBy('NAME','DESC')->lists('NAME', 'id');
          $owners = Property::on($database )->orderBy('strOwners','DESC')->groupBy('strOwners')->lists('strOwners', 'id');
        $properties = Property::on($database )->orderBy('strKey','ASC')->lists('strkey', 'id');
        $erfs = Note::on($database )->orderBy('numErf','ASC')->lists('numErf', 'id');


        return view('search',compact('streets','complexes','owners','properties','erfs'));
    }
}
