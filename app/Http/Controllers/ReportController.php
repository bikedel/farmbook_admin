<?php

namespace App\Http\Controllers;

use App\Property;
use App\Street;
use App\User;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use Redirect;
use Session;
use Storage;

class ReportController extends Controller
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
        $street = new Street;
        $street->changeConnection($database);

        //change database for Street
        $property = new Property;
        $property->changeConnection($database);

        // get inputs
        $Input  = $request->input('input');
        $Select = $request->input('selected');

//dd(   $streetInput,$streetSelect);
        // check if input or select
        // if input ignore select

        if (strlen($Input) > 0) {
            // search
            $search     = $Input;
            $properties = Property::on($database)->like('strStreetName', $search)->orderby('strStreetName', 'ASC')->orderby('numStreetNo', 'ASC')->get();

        } else {
            // search
            $street     = Street::on($database)->where('id', $Select)->first();
            $search     = $street->strStreetName;
            $properties = Property::on($database)->where('strStreetName', $search)->orderby('strStreetName', 'ASC')->orderby('numStreetNo', 'ASC')->get();

        }

        {
            Session::put('search', $Select);
            Session::put('controllerroute', '/street');
        }

        // view properties
        // return with error if no result
        if ($properties->count()) {
            return view('streets', compact('properties', 'search'));
        } else {
            Session::flash('flash_message', '' . "No properties matching search criteria.");
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back();
        }

    }

    // edit all
    public function printreport($id)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();
            $email    = Auth::user()->email;

            //log
            $action  = 'PRINTING';
            $comment = 'Report for ' . $database . ' - ' . $id;
            $append  = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',' . $comment;
            Storage::append('logfile.txt', $append);

            //change database
            $property = new Property;
            $property->changeConnection($database);

            // search on street name
            $query      = Property::on($database)->like('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('strStreetNo', 'ASC')->get();
            $properties = Property::on($database)->like('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('numStreetNo', 'ASC')->get();

            // get relationship data
            $properties->load('owner', 'note');

            // get total records as simplepagination does not do this
            $count  = $query->count();
            $search = $id;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        return view('report', compact('properties', 'count', 'search'));

    }

    public function printreportNew($id)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();
            $email    = Auth::user()->email;

            //log
            $action  = 'PRINTING';
            $comment = 'Report for ' . $database . ' - ' . $id;
            $append  = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',' . $comment;
            Storage::append('logfile.txt', $append);

            //change database
            $property = new Property;
            $property->changeConnection($database);

            // search on street name
            $query      = Property::on($database)->like('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('strStreetNo', 'ASC')->get();
            $properties = Property::on($database)->like('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('numStreetNo', 'ASC')->get();

            // get relationship data
            $properties->load('owner', 'note');

            // get total records as simplepagination does not do this
            $count  = $query->count();
            $search = $id;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        return view('reportNew', compact('properties', 'count', 'search'));

    }

    public function printbycomplexreport($id)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();
            $email    = Auth::user()->email;

            //log
            $action  = 'PRINTING';
            $comment = 'Report for ' . $database . ' - ' . $id;
            $append  = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',' . $comment;
            Storage::append('logfile.txt', $append);

            //change database
            $property = new Property;
            $property->changeConnection($database);

            // search on street name
            $query      = Property::on($database)->like('strComplexName', $id)->orderby('strComplexName', 'ASC')->orderby('strComplexNo', 'ASC')->get();
            $properties = Property::on($database)->like('strComplexName', $id)->orderby('strComplexName', 'ASC')->orderby('numComplexNo', 'ASC')->get();

            // get relationship data
            $properties->load('owner', 'note');

            // get total records as simplepagination does not do this
            $count  = $query->count();
            $search = $id;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        return view('reportByComplex', compact('properties', 'count', 'search'));
    }

    // only print when notes contains 'wrote'

    public function printbycomplexreportNew($id)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();
            $email    = Auth::user()->email;

            //log
            $action  = 'PRINTING';
            $comment = 'Report for ' . $database . ' - ' . $id;
            $append  = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',' . $comment;
            Storage::append('logfile.txt', $append);

            //change database
            $property = new Property;
            $property->changeConnection($database);

            // search on street name
            $query      = Property::on($database)->like('strComplexName', $id)->orderby('strComplexName', 'ASC')->orderby('strComplexNo', 'ASC')->get();
            $properties = Property::on($database)->like('strComplexName', $id)->orderby('strComplexName', 'ASC')->orderby('numComplexNo', 'ASC')->get();

            // get relationship data
            $properties->load('owner', 'note');

            // get total records as simplepagination does not do this
            $count  = $query->count();
            $search = $id;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        return view('reportByComplexNew', compact('properties', 'count', 'search'));
    }

    public function printupdates()
    {

        // set database
        $database = Auth::user()->getDatabase();
        $email    = Auth::user()->email;

        //change database
        $property = new Property;
        $property->changeConnection($database);

        //
        $properties = Property::on($database)->get();

        // get relationship data
        $properties->load('owner', 'note');
        echo $properties->count() . '<br>';
        $t = $properties->where('note->memNotes', 'like', '%wrote%');
        echo $t->count() . '<br>';
        // convert to array
        //
        //$props = $properties->toArray();

        //for ($x = 0; $x <= sizeof($props); $x++) {

        foreach ($properties as $prop) {

            if ($prop->note) {
                if (strpos($prop->note->memNotes, 'wrote') !== false) {
                    // echo $prop->note->memNotes . '<br>';

                    $m         = ($prop->note->memNotes);
                    $my_string = preg_replace(array('/\n/'), '#PH#', $m);
                    $m         = explode('#PH#', $my_string);
                    if ($prop->strKey == '49091-0') {
                        dd($m);
                    }
                }
            } else {
                echo $prop->strKey . '<br>';
                // echo key($prop);

            }

        }

        dd("print updates - reportcontroller", $properties);
    }

    public function testreport()
    {
        $users = User::all();

        $bar = $this->output->createProgressBar(count($users));

        foreach ($users as $user) {
            // $this->performTask($user);

            $bar->advance();
        }

        $bar->finish();

    }

}
