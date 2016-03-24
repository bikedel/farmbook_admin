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
            $action = 'Printing Report for ' . $id;
            $append = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . '          ' . trim($email) . '          ' . $action;
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
