<?php

namespace App\Http\Controllers;

use App\CsvFileUpdater;
use App\Note;
use App\Owner;
use App\Property;
use App\Update;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Redirect;
use Session;

class UpdateController extends Controller
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

    // FH Update
    //
    public function update(Request $request)
    {

        //  check that a file has been selected
        //
        if (!$request->hasFile('csv_update')) {
            $message = 'Please select a CSV file to update with.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        $csv_file = $request->file('csv_update');

        $filename = $csv_file->getClientOriginalName();
        $fileinfo = pathinfo($filename);

        // check the file has a .csv extention
        //
        if (strtoupper($fileinfo['extension']) !== 'CSV') {
            $message = 'You did not select a csv file.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        //  determine if the type is FH or ST
        //
        $valid = 0;
        if (strpos($fileinfo['filename'], 'FH') !== false) {
            $type  = "FH";
            $valid = 1;
        }

        //
        //
        //  disallow as not implemented
        //
        if (strpos($fileinfo['filename'], 'ST') !== false) {
            $type  = "ST";
            $valid = 0;
        }

        // if not FH or ST the return error
        //
        if ($valid !== 1) {
            $message = 'Please provide a valid SAPTG file - ST not implemented yet.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        // get the databse name from the CSV file
        //
        $startpos    = strrpos($fileinfo['filename'], '_') + 1;
        $endpos      = strrpos($fileinfo['filename'], ' ');
        $len         = $endpos - $startpos;
        $name        = substr($fileinfo['filename'], $startpos, $len);
        $normal_name = $name . ' ' . $type;
        $name        = str_replace(' ', '_', $name) . '_' . $type . '_' . 'farmbook2';
        $database    = $name;

        // check if the database exists
        $schema = 'information_schema';
        $otf    = new \App\Database\OTF(['database' => $schema]);
        $db     = DB::connection($schema);
        $data   = $db->table('schemata')->select('schema_name')->where('schema_name', 'like', '%farmb%')->orderBy('schema_name')->lists("schema_name", "schema_name");
        $found  = array_search($database, $data);

        //  the database does not exist
        //
        if ($found == false) {
            $message = 'The database does not exist - ' . $database;
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        // create update instance
        //
        $csv_updater = new CsvFileUpdater();

        // Import our csv file
        if (!$csv_updater->update($csv_file, $database)) {
            $message = 'Error importing the file during update.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        //  dd($data, $database, $found);
        //  dd('stop');

        // connect to the database
        //
        $dbname = $database;
        $otf    = new \App\Database\OTF(['database' => $dbname]);
        $db     = DB::connection($dbname);

        $del        = 0;
        $add        = 0;
        $tot        = 0;
        $lastupdate = "none";
        $now        = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // setup log file - same name as csv with .log ext
        $destination_directory = storage_path('updates/tmp');
        $original_file_name    = $csv_file->getClientOriginalName();
        $logfilename           = $destination_directory . '/' . $original_file_name;
        $logfilename           = str_replace('.csv', '.log', $logfilename);

        File::put($logfilename, '');

        // get all update records
        $updates = Update::on($database)->orderBy('strKey')->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->get();

        // convert to array
        $updatesA = $updates->toArray();

        // process update records
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // check if there are newer transactions in properties
            $p = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '>', Date($updatesA[$x]['dtmRegDate']))->get();

            // insert and delete older as it is the newest
            if ($p->count() == 0) {

                $echo = $updatesA[$x]['strKey'] . '   -   ' . $updatesA[$x]['dtmRegDate'] . "  -  New Owner - " . $updatesA[$x]['strOwners'] . "  -  Seller - " . $updatesA[$x]['strSellers'];

                File::append($logfilename, $echo . "\r\n");

                $updatesA[$x]['id']           = null;
                $updatesA[$x]['numStreetNo']  = $updatesA[$x]['strStreetNo'];
                $updatesA[$x]['numComplexNo'] = $updatesA[$x]['strComplexNo'];

                //add updated at to end of the array
                $updatesA[$x]['updated_at'] = $now;

                // check the record does not already exist
                $check = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where('strIdentity', '=', $updatesA[$x]['strIdentity'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '=', Date($updatesA[$x]['dtmRegDate']))->get();

                if ($check->count() == 0) {
                    $add++;
                    Property::on($database)->insert($updatesA[$x]);

                    // add owner record if not existing
                    $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA[$x]['strIdentity']);
                    if ($hascontact->count() == 0) {
                        $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA[$x]['strIdentity'], 'NAME' => $updatesA[$x]['strOwners'], 'updated_at' => $now));
                    } else {
                        // dont wipe old details

                    }
                    // add note
                    $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA[$x]['strKey'])->get();
                    if ($hasnote->count() == 0) {
                        $note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => "\n" . $now . '  ' . $updatesA[$x]['strOwners'] . '  - New Owner.', 'updated_at' => $now));
                        $lastupdate = $updatesA[$x]['strKey'];
                    } else {
                        // only add note addendum once for the strKey - lastupdate
                        if ($lastupdate != $updatesA[$x]['strKey']) {
                            $note       = Note::on($database)->where('strKey', '=', $updatesA[$x]['strKey'])->update(array('memNotes' => DB::raw('concat(memNotes, " \n' . $now . '  ' . $updatesA[$x]['strOwners'] . ' -  New Owner.")'), 'updated_at' => $now));
                            $lastupdate = $updatesA[$x]['strKey'];
                        }
                    }

                } else {
                    $echo = "----- " . "Already in database    " . $updatesA[$x]['strKey'];
                    File::append($logfilename, $echo . "\r\n");
                }

                // get the properties we will delete for the report
                $delp = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

                // delete them
                Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->delete();

                // report
                $properties = $delp->toArray();
                for ($i = 0; $i <= sizeof($properties) - 1; $i++) {
                    $del++;
                    $echo = "---" . $properties[$i]['strKey'] . '   -   ' . $properties[$i]['dtmRegDate'] . "  - Old owner - " . $properties[$i]['strOwners'];
                    File::append($logfilename, $echo . "\r\n");
                }

            }

        }

        $echo = 'Deleted : ' . $del;
        File::append($logfilename, $echo . "\r\n");
        $echo = 'Added : ' . $add;
        File::append($logfilename, $echo . "\r\n");
        $echo = 'Total : ' . ($add - $del);
        File::append($logfilename, $echo . "\r\n");

//        File::append($filename,

        //  echo $logfilename;

        $message = 'Update completed.';
        Session::flash('flash_type', 'alert-success');
        return Redirect::back()->with('flash_message', $message);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {

    }

}
