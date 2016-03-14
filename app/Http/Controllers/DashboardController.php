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
use App\User;
use App\Contact;
use File;

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
       // $query = Property::on(   $database)->select('*')->get();



        $query1 = Property::on(   $database)->select(  [DB::raw('cast(dtmRegDate as date) as dd'),DB::raw('count(dtmRegDate) as sales') ,DB::raw('max(strAmount) as high'),DB::raw('avg(strAmount) as avg')])->groupBy(DB::raw('Year(dd)'))->get();


        $min = $query1->min('dd');
        $max = $query1->max('dd');


 //$query1->groupBy('Year(dd)');
        



        $dateStart = Carbon::createFromFormat('Y-m-d', $min);
        $dateEnd = Carbon::createFromFormat('Y-m-d', $max);

        $diffInYears = $dateStart->diffInYears($dateEnd, false);





        $Amin = Property::on(   $database)->min('strAmount');
        $Amax =  Property::on(   $database)->max('strAmount');

        $sum = Property::on(   $database)->sum('strAmount');
        $avg = Property::on(   $database)->where('strAmount','>',0)->avg('strAmount');




       // dd($min,$max,$Amin,$Amax,$sum,$avg,$dateStart,$dateEnd,$diffInYears);

        $stocksTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

        $stocksTable->addStringColumn('Date')
        ->addNumberColumn('Registered');
                  //  ->addNumberColumn('Bond');

        // Random Data For Example


        foreach($query1 as &$q)
        {
  //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
  //  echo "<br>";
         $mdate = intval(substr($q->dd,0,4));

         if ($mdate > 2004){
           // dd($q->dd);
            $stocksTable->addRow([
             $mdate  , $q->sales
             ]);
        }
    }


        $priceTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

        $priceTable->addStringColumn('Date')
        ->addNumberColumn('Avg Price')
        ;

        foreach($query1 as $key =>$q)
        {
  //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
  //  echo "<br>";
         $mdate = intval(substr($q->dd,0,4));

         if ($mdate > 2004){
            $priceTable->addRow([
             $mdate , intval($q->avg)
             ]);
        }}




  $c1 = Street::on(   $database)->count('id');

 $c2 = Complex::on(   $database)->count('id');

   $c3 = Property::on(   $database)->select('id')->groupBy('strKey')->get()->count();

  $c4 = Property::on(   $database)->count('id');



 $votes  = Lava::DataTable();

 $votes->addStringColumn('Food Poll')
 ->addNumberColumn('Count')
 ->addRow(['Streets',  $c1])
 ->addRow(['Complexes',  $c2])
 ->addRow(['Properties',  $c3])
 ->addRow(['Owners', $c4])


 ;





//dd($query1,$min,$max,$diffInYears );

//dd();
//$chart = $lava->LineChart('MyStocks', $stocksTable);

//$chart2 = Lava::LineChart('Prices', $priceTable); //if using Laravel
 $chart = Lava::LineChart('Registrations', $stocksTable, [
    'title' => "Properties registered ",
    'colors' => ['blue'],

    ]);



 $chart2 = Lava::LineChart('Prices', $priceTable, [
    'title' => "Average price ",
    'colors' => ['green','red'],
    'vAxis' => ['format' => 'R###,###,###,###'],
    ]);


 $chart2 = Lava::BarChart('Votes', $votes, [
    'title' => "Totals ",
    'colors' => ['DeepSkyBlue']

    ]);
 //$chart3 = Lava::BarChart('Votes', $votes);



 return view('dashboard',compact('chart','chart2'));

}
}
