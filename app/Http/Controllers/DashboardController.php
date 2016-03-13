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
        $query = Property::on(   $database)->select('*')->get();



        $query1 = Property::on(   $database)->select(  [DB::raw('cast(dtmRegDate as date) as dd'),DB::raw('count(dtmRegDate) as sales') ,DB::raw('max(strBondAmount) as low'),DB::raw('max(strAmount) as high')])->groupBy(DB::raw('Year(dd)'))->get();


        $min = $query1->min('dd');
        $max = $query1->max('dd');


 //$query1->groupBy('Year(dd)');
        



        $dateStart = Carbon::createFromFormat('Y-m-d', $min);
        $dateEnd = Carbon::createFromFormat('Y-m-d', $max);

        $diffInYears = $dateStart->diffInYears($dateEnd, false);





        $Amin = Property::on(   $database)->min('strAmount');
        $Amax =  Property::on(   $database)->max('strAmount');

        $sum = Property::on(   $database)->sum('strAmount');
        $avg = Property::on(   $database)->where('strAmount')->avg('strAmount');




       // dd($min,$max,$Amin,$Amax,$sum,$avg,$dateStart,$dateEnd,$diffInYears);

        $stocksTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

        $stocksTable->addDateColumn('Date')
        ->addNumberColumn('Registered');
                  //  ->addNumberColumn('Bond');

        // Random Data For Example


        foreach($query1 as &$q)
        {
  //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
  //  echo "<br>";
            $stocksTable->addRow([
               $q->dd  , $q->sales
               ]);
        }



        $priceTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

        $priceTable->addDateColumn('Date')
        ->addNumberColumn('Price');

        foreach($query1 as &$q)
        {
  //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
  //  echo "<br>";
            $priceTable->addRow([
               $q->dd  , $q->high
               ]);
        }


$u = User::count();
$f = Farmbook::count();
$c = Contact::count();
$filename = storage_path().'/app/'.'logfile.txt';
$content = File::get($filename);

$logs = explode(PHP_EOL, $content);


 $junkTable = Lava::DataTable();  // Lava::DataTable() if using Laravel

 $junkTable->addStringColumn('Type')
 ->addNumberColumn('Value')
 ->addRow(['Users', $u])
 ->addRow(['Databases', $f])
 ->addRow(['Contacts', $c])
 ->addRow(['Logs',sizeof($logs)]);

 $chart3 = Lava::GaugeChart('Temps', $junkTable, [
    'width'      => 400,
    'greenFrom'  => 0,
    'greenTo'    => 69,
    'yellowFrom' => 70,
    'yellowTo'   => 89,
    'redFrom'    => 90,
    'redTo'      => 100,
    'majorTicks' => [
    'Safe',
    'Critical'
    ]
    ]);






//dd($query1,$min,$max,$diffInYears );

//dd();
//$chart = $lava->LineChart('MyStocks', $stocksTable);
 $chart = Lava::LineChart('Registrations', $stocksTable); //if using Laravel
$chart2 = Lava::LineChart('Prices', $priceTable); //if using Laravel











return view('dashboard',compact('chart','chart2'));

}
}
