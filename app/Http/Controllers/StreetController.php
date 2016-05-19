<?php

namespace App\Http\Controllers;

use App\Property;
use App\Street;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Redirect;
use Session;

class StreetController extends Controller
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
        //$updatesonly = $request->input('updates');

        //dd($updatesonly);
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
    public function rolledit($id, Request $request)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();

            //change database
            $property = new Property;
            $property->changeConnection($database);

            // search on street name
            $query      = Property::on($database)->where('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('strStreetNo', 'ASC')->get();
            $properties = Property::on($database)->where('strStreetName', $id)->orderby('strStreetName', 'ASC')->orderby('numStreetNo', 'ASC')->simplePaginate(1);

            // get streets and prepend selected street

            $streets = Street::on($database)->orderBy('strStreetName', 'ASC')->lists('strStreetName', 'strStreetName');
            // $streets->prepend(['selected' => $streetname]);

            // get relationship data
            $properties->load('owner', 'note');

            // get total records as simplepagination does not do this
            $count  = $query->count();
            $search = $id;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        //dd($properties,$count);

        return view('property', compact('properties', 'count', 'search', 'streets'));

    }

    public function add(Request $request)
    {

        // set database
        $database = Auth::user()->getDatabase();

        //change database
        $property = new Property;
        $property->changeConnection($database);

        // input street name
        $newstreet = $request->input('street');

        //dd($newstreet,$request);
        // check if street exists
        $query = Street::on($database)->where('strStreetName', $newstreet)->first();

        //dd(strlen($newstreet));

        if (is_null($query) && strlen($newstreet) > 0) {
            try
            {

                Session::flash('flash_message', '' . "Ok to add as not found");
                Session::flash('flash_type', 'alert-success');
            } catch (exception $e) {
                Session::flash('flash_message', '' . $e->getMessage());
                Session::flash('flash_type', 'alert-danger');
            }
        } else {

            Session::flash('flash_message', '' . "Street already exists.");
            Session::flash('flash_type', 'alert-danger');

        }

        return Redirect::back();

    }

}
