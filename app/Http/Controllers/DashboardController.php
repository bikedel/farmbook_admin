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



        $query1 = Property::on(   $database)->select(  [DB::raw('cast(dtmRegDate as date) as dd'),DB::raw('count(dtmRegDate) as sales') ,DB::raw('max(strAmount) as high'),DB::raw('avg(strAmount) as avg')])->groupBy(DB::raw('Year(dd)'))->where('strAmount','>',0)->get();


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
        ->addNumberColumn('Avg Price')
       ;

        foreach($query1 as &$q)
        {
  //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
  //  echo "<br>";
            $priceTable->addRow([
               $q->dd  , intval($q->avg)
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
    'width'      => 600,
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





$votes  = Lava::DataTable();

$votes->addStringColumn('Food Poll')
      ->addNumberColumn('Votes')
      ->addRow(['Tacos',  rand(1000,5000)])
      ->addRow(['Salad',  rand(1000,5000)])
      ->addRow(['Pizza',  rand(1000,5000)])
      ->addRow(['Apples', rand(1000,5000)])
      ->addRow(['Fish',   rand(1000,5000)])

      ;













//dd($query1,$min,$max,$diffInYears );

//dd();
//$chart = $lava->LineChart('MyStocks', $stocksTable);

//$chart2 = Lava::LineChart('Prices', $priceTable); //if using Laravel
    $chart = Lava::LineChart('Registrations', $stocksTable, [
        'title' => "Properties registered per Year",
        'colors' => ['blue'],
   
    ]);



    $chart2 = Lava::LineChart('Prices', $priceTable, [
        'title' => "Average Price for the Year",
        'colors' => ['green','red'],
        'vAxis' => ['format' => 'R###,###,###,###'],
    ]);



$chart3 = Lava::BarChart('Votes', $votes);



return view('dashboard',compact('chart','chart2'));

}
}
