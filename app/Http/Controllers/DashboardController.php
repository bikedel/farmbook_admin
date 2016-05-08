<?php

namespace App\Http\Controllers;

use App\Complex;
use App\Farmbook;
use App\Note;
use App\Property;
use App\Street;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Lava;

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

// todo - search database for upcoming actions

    public function todo()
    {

        // get all note with a follow up date TODAY - 1 WEEK
        $now = \Carbon\Carbon::now('Africa/Johannesburg')->subWeeks(1)->toDateTimeString();
        //$now->subWeeks(1);
        // set database
        $database = Auth::user()->getDatabase();

        //change database
        $note = new Note;
        $note->changeConnection($database);

        $followups = Note::on($database)->select('*')->where('followup', '>=', $now)->orderBy('followup')->get();

        $owners = array();

        // loop all notes with date > today
        foreach ($followups as $followup) {

            for ($x = 0; $x < $followup->properties->count(); $x++) {
                $detail = ['id' => $followup->properties[$x]->id,
                    'strKey'        => $followup->properties[$x]->strKey,
                    'strOwners'     => $followup->properties[$x]->strOwners,
                    'memNotes'      => $followup->memNotes,
                    'followup'      => $followup->followup];

                array_push($owners, $detail);
            }
        }
        // dd($followups, "follow up - dashboard controller");
        //dd($owners);
        return view('followups', compact('followups', 'owners'));

    }

    // loop through all database
    public function glob()
    {

        $farmbooks = Farmbook::orderBy('name')->get();
        $nousers   = User::orderBy('name')->get();
        echo "<b><br>" . $farmbooks->count() . " = " . "Farmbooks" . "<br>";
        echo $nousers->count() . " = " . "Users" . "</b><br>";

        for ($x = 0; $x < $farmbooks->count(); $x++) {
            $error    = 0;
            $database = $farmbooks[$x]->database;
            try {
                //change database
                $prop = new Property;
                $prop->changeConnection($database);

                $lastdate = Property::on($database)->select('dtmRegDate')->orderBy('dtmRegDate', 'desc')->first();

            } catch (Exception $ex) {
                echo "<br> ------------------------------------------------------------------" . "<br>";
                echo $x . ". " . $farmbooks[$x]->database . " <br>";
                echo " **  PROBLEM **  " . $ex->getMessage() . "<br>";
                echo " ------------------------------------------------------------------" . "<br>";

                $error = 1;
                //dd();
            }
            //dd($prop);

            if ($error == 0) {

                // check it has the farmbook2 ext

                $found = strpos($database, 'farmmbook2');

                echo "<br> ------------------------------------------------------------------" . "<br>";
                if ($found > 0) {
                    echo "** ALERT **  Database has not got correct naming convention - farmbook2" . " <br>";
                }
                echo $x . ". " . $farmbooks[$x]->database . " <br>";
                echo " - " . $lastdate->dtmRegDate . " <-  Last Registration  " . " <br>";
                echo " ------------------------------------------------------------------" . "<br>";
                $users = $farmbooks[$x]->users;
                foreach ($users as $user) {
                    echo " - " . $user->name . " [" . $user->email . "]<br>";
                }
            }

        }
        dd("The End");
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
        $property->changeConnection($database);

        $query1 = Property::on($database)->select([DB::raw('cast(dtmRegDate as date) as dd'), DB::raw('count(dtmRegDate) as sales'), DB::raw('max(strAmount) as high'), DB::raw('avg(strAmount) as avg')])->groupBy(DB::raw('Year(dd)'))->get();

        $min = $query1->min('dd');
        $max = $query1->max('dd');

        $dateStart = Carbon::createFromFormat('Y-m-d', $min);
        $dateEnd   = Carbon::createFromFormat('Y-m-d', $max);

        $diffInYears = $dateStart->diffInYears($dateEnd, false);

        $Amin = Property::on($database)->min('strAmount');
        $Amax = Property::on($database)->max('strAmount');

        $sum = Property::on($database)->sum('strAmount');
        $avg = Property::on($database)->where('strAmount', '>', 0)->avg('strAmount');

        // dd($min,$max,$Amin,$Amax,$sum,$avg,$dateStart,$dateEnd,$diffInYears);

        $stocksTable = Lava::DataTable(); // Lava::DataTable() if using Laravel

        $stocksTable->addStringColumn('Date')
            ->addNumberColumn('Registered');
        //  ->addNumberColumn('Bond');

        // Random Data For Example

        foreach ($query1 as &$q) {
            //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
            //  echo "<br>";
            $mdate = intval(substr($q->dd, 0, 4));

            if ($mdate > 2004) {
                // dd($q->dd);
                $stocksTable->addRow([
                    $mdate, $q->sales,
                ]);
            }
        }

        // chart - price
        //-------------------------------------------------------------------
        $priceTable = Lava::DataTable(); // Lava::DataTable() if using Laravel

        $priceTable->addStringColumn('Date')
            ->addNumberColumn('Avg Price')
        ;

        foreach ($query1 as $key => $q) {
            //  echo  substr($q->dd,0,4).'    -    '. $q->sales;
            //  echo "<br>";
            $mdate = intval(substr($q->dd, 0, 4));

            if ($mdate > 2004) {
                $priceTable->addRow([
                    $mdate, intval($q->avg),
                ]);
            }}

        $c1 = Street::on($database)->count('id');

        $c2 = Complex::on($database)->count('id');

        $c3 = Property::on($database)->select('id')->groupBy('strKey')->get()->count();

        $c4 = Property::on($database)->count('id');

        $votes = Lava::DataTable();

        $votes->addStringColumn('Food Poll')
            ->addNumberColumn('Count')
            ->addRow(['Streets', $c1])
            ->addRow(['Complexes', $c2])
            ->addRow(['Properties', $c3])
            ->addRow(['Owners', $c4]);

        // chart -age
        //-------------------------------------------------------------------

        $queryAge = Property::on($database)->select('strKey', 'strSellers', 'dtmRegDate')->distinct('strIdentity')->OrderBy('dtmRegDate')->GroupBy('dtmRegDate')->get();

        $queryAge = $queryAge->toArray();

        // dd($queryAge);
        $ageTable = Lava::DataTable(); // Lava::DataTable() if using Laravel

        $ageTable->addStringColumn('Date')
            ->addNumberColumn('Avg Age')
        ;

        $agedate = array();
        for ($x = 0; $x <= sizeof($queryAge) - 1; $x++) {

            $seller = substr($queryAge[$x]['strSellers'], -13);

            if (is_numeric($seller) && strlen(ltrim(rtrim($seller))) == 13) {

                $born = substr($seller, 0, 2);
                $age  = 116 - $born;

                $mdate = intval(substr($queryAge[$x]['dtmRegDate'], 0, 4));

                //  echo $queryAge[$x]['strKey'] . "   -   " . $mdate . "  -  " . $seller . ' - ' . $age . " <br>";
                $agegate = array_push($agedate, ['date' => $mdate, 'age' => $age]);

            }
        }

        $collection = collect($agedate);

        $coll = $collection->groupBy('date');

        //dd($coll);

        $ageTable->addRow([$mdate, $age]);

        //  dd($queryAge, $queryAge->count(), $seller);

        $agechart = Lava::LineChart('Ages', $ageTable, [
            'title'  => "Sellers Age ",
            'colors' => ['Cyan'],

        ]);

        $chart = Lava::LineChart('Registrations', $stocksTable, [
            'title'  => "Properties registered ",
            'colors' => ['blue'],

        ]);

        $chart2 = Lava::LineChart('Prices', $priceTable, [
            'title'  => "Average price ",
            'colors' => ['green', 'red'],
            'vAxis'  => ['format' => 'R###,###,###,###'],
        ]);

        $chart3 = Lava::BarChart('Votes', $votes, [
            'title'  => "Totals ",
            'colors' => ['DeepSkyBlue'],

        ]);
        //$chart3 = Lava::BarChart('Votes', $votes);

        return view('dashboard');

    }
}
