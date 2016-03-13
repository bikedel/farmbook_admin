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
use Lava;
use Carbon\Carbon;

class DashboardController extends Controller
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



        // set database
        $database = Auth::user()->getDatabase();

       //change database
        $property = new Property;
        $property->changeConnection(    $database  );

        // search on street name
        $query = Property::on(   $database)->select('*')->get();



 $query1 = Property::on(   $database)->select(  [DB::raw('cast(dtmRegDate as decimal) as dd'),DB::raw('count(dtmRegDate) as sales') ,DB::raw('max(strBondAmount) as low'),DB::raw('max(strAmount) as high')])->groupBy('dd')->get();


        $min = $query1->min('dd');
        $max = $query1->max('dd');



        



$dateStart = Carbon::createFromFormat('Y', $min);
$dateEnd = Carbon::createFromFormat('Y', $max);

$diffInYears = $dateStart->diffInYears($dateEnd, false);





        $Amin = Property::on(   $database)->min('strAmount');
        $Amax =  Property::on(   $database)->max('strAmount');

        $sum = Property::on(   $database)->sum('strAmount');
        $avg = Property::on(   $database)->where('strAmount')->avg('strAmount');




       // dd($min,$max,$Amin,$Amax,$sum,$avg,$dateStart,$dateEnd,$diffInYears);

        $stocksTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

        $stocksTable->addDateColumn('Year')
                    ->addNumberColumn('Registered');
                  //  ->addNumberColumn('Bond');

        // Random Data For Example


 foreach($query1 as &$q)
 {
            $stocksTable->addRow([

              $q->dd , $q->sales
            ]);
 }
//dd($query1,$min,$max,$diffInYears );


//$chart = $lava->LineChart('MyStocks', $stocksTable);
 $chart = Lava::LineChart('MyStocks', $stocksTable); //if using Laravel

   return view('dashboard',compact('chart'));

    }
}
