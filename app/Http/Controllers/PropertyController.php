<?php

namespace App\Http\Controllers;

use App\Note;
use App\Owner;
use App\Property;
use App\Street;
use App\User;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use Redirect;
use Session;

class PropertyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    //  edit property
    public function edit($id)
    {
        try {

            // set database
            $database = Auth::user()->getDatabase();

            //change database
            $property = new Property;
            $property->changeConnection($database);

            $properties = Property::on($database)->where('id', $id)->paginate(1);
            $properties->load('owner', 'note');

            // get all streets
            $streets = Street::on($database)->orderBy('strStreetName', 'ASC')->lists('strStreetName', 'strStreetName');

            // pass searched string
            $search = $id;

            $count = 1;

        } catch (exception $e) {
            dd($e->getMessage());
        }

        return view('property', compact('properties', 'count', 'search', 'streets'));

    }

    // update the owners data and the notes
    public function update(Request $request)
    {

        // set database
        $database = Auth::user()->getDatabase();

        //change database
        $owner = new Owner;
        $owner->changeConnection($database);

        //change database
        $note = new Note;
        $note->changeConnection($database);

        // get logged in user
        $user = Auth::user()->name;
        $now  = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();

        // get inputs
        $strKey      = $request->input('strKey');
        $strIdentity = $request->input('strIdentity');
        $strOwners   = $request->input('strOwners');

        $homePhone = $request->input('strHomePhoneNo');
        $workPhone = $request->input('strWorkPhoneNo');
        $cellPhone = $request->input('strCellPhoneNo');
        $email     = $request->input('EMAIL');
        $note      = $request->input('note');
        $newnote   = $request->input('newnote');

        $strStreetNo   = $request->input('strStreetNo');
        $strStreetName = $request->input('strStreetName');

        $followup = $request->input('followup');
        $date     = "";
        if (strLen($followup) > 0) {
            $date = Carbon\Carbon::createFromFormat('Y-m-d', $followup);
        }

        try {

            // update personal details
            //   $owner = Owner::on( $database )->where('strIDNumber', $strIdentity)->update(array('strCellPhoneNo' => $cellPhone,
            //      'strHomePhoneNo' => $homePhone,
            //      'strWorkPhoneNo' => $workPhone,
            //      'EMAIL' => $email,
            //      'updated_at'=> $now
            //      ));

            $properties = Property::on($database)->where('strKey', $strKey)->update(array('strStreetNo' => $strStreetNo, 'numStreetNo' => $strStreetNo, 'strStreetName' => $strStreetName));

//dd($properties);

            //update owner details

            $owner = Owner::on($database)->where('strIDNumber', $strIdentity)->first();

            $owner->strHomePhoneNo = $homePhone;
            $owner->strCellPhoneNo = $cellPhone;
            $owner->strWorkPhoneNo = $workPhone;
            $owner->EMAIL          = $email;

            $owner->save();

            // check if there is a new note
            if (strlen($newnote) > 0) {
                // if a previous note exists add a carrige return and new note
                if (strlen($note) > 0) {
                    $updatednote = ltrim(rtrim($note)) . "\n" . $now . " " . $user . " wrote: " . "\n" . $newnote;
                } else {
                    // add just the new note
                    $updatednote = $now . " " . $user . " wrote: " . "\n" . $newnote;
                }

                // update the note
                $affected = Note::on($database)->where('strKey', $strKey)->update(array('memNotes' => $updatednote, 'followup' => $date, 'updated_at' => $now));
            }

            Note::on($database)->where('strKey', $strKey)->update(array('followup' => $date, 'updated_at' => $now));

        } catch (exception $e) {

            Session::flash('flash_message', 'Error ' . $e->getMessage());
            Session::flash('flash_type', 'alert-danger');

            return Redirect::back();

        }

        Session::flash('flash_message', 'Updated ' . $strOwners . ' at ' . $now);
        Session::flash('flash_type', 'alert-success');

        return Redirect::back();

    }

}
