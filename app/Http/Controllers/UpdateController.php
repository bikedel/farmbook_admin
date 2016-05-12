<?php

namespace App\Http\Controllers;

use App\Contact;
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
        $this->logfilename = '';
    }

    // FH Update
    //
    public function updateFH(Request $request)
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
        //
        if (strpos($fileinfo['filename'], 'ST') !== false) {
            $type  = "ST";
            $valid = 0;
        }

        // if not FH or ST the return error
        //
        if ($valid !== 1) {
            $message = 'This is not a valid FH update file.';
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
        //
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
        //
        if (!$csv_updater->update($csv_file, $database)) {
            $message = 'Error importing the file during update.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        // connect to the database
        //
        $dbname = $database;
        $otf    = new \App\Database\OTF(['database' => $dbname]);
        $db     = DB::connection($dbname);

        // totals
        $del        = 0;
        $add        = 0;
        $tot        = 0;
        $lastupdate = "none";

        // time
        $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // setup log file - same name as csv with .log ext
        $destination_directory = storage_path('updates/tmp');
        $original_file_name    = $csv_file->getClientOriginalName();
        $logfilename           = $destination_directory . '/' . $original_file_name;
        $logfilename           = str_replace('.csv', '.log', $logfilename);

        File::put($logfilename, '');

        // check latest regDate in properties and delete old updates
        $propDate = Property::on($database)->orderBy('dtmRegDate', 'desc')->first();

        Update::on($database)->where('dtmRegDate', '<=', $propDate['dtmRegDate'])->delete();

        $echo = "last dtmRegDate in properties = " . $propDate['dtmRegDate'];
        File::append($logfilename, $echo . "\r\n");

        // get all update records
        //
        $updates = Update::on($database)->orderBy('strKey')->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->get();

        // convert to array
        //
        $updatesA = $updates->toArray();

        // process update records
        //
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // check if there are newer transactions in properties
            //
            $p = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '>', Date($updatesA[$x]['dtmRegDate']))->get();

            // insert and delete older as it is the newest
            //

            //  dd($p->count(), $p);

            if ($p->count() == 0) {

                $echo = $updatesA[$x]['strKey'] . '   -   ' . $updatesA[$x]['dtmRegDate'] . "  -  New Owner - " . $updatesA[$x]['strOwners'] . "  -  Seller - " . $updatesA[$x]['strSellers'];

                File::append($logfilename, $echo . "\r\n");

                $updatesA[$x]['id']           = null;
                $updatesA[$x]['numStreetNo']  = $updatesA[$x]['strStreetNo'];
                $updatesA[$x]['numComplexNo'] = $updatesA[$x]['strComplexNo'];

                //add updated at to end of the array
                //
                $updatesA[$x]['updated_at'] = $now;

                // check the record does not already exist
                //
                $check = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where('strIdentity', '=', $updatesA[$x]['strIdentity'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '=', Date($updatesA[$x]['dtmRegDate']))->get();

                if ($check->count() == 0) {
                    $add++;
                    Property::on($database)->insert($updatesA[$x]);

                    // add owner record if not existing
                    //
                    $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA[$x]['strIdentity']);
                    if ($hascontact->count() == 0) {

                        // update details from admin contacts
                        $admin_contacts = "farmbook_admin";
                        $otf            = new \App\Database\OTF(['database' => $admin_contacts]);
                        $db             = DB::connection($admin_contacts);

                        $owner_details = Contact::on($admin_contacts)->select('*')->where('strIDNumber', $updatesA[$x]['strIdentity'])->first();

                        // contact details found in admin contacts
                        if (sizeof($owner_details) == 1) {
                            $uid = $owner_details->strIDNumber;

                            // set database back
                            $dbname = $database;
                            $otf    = new \App\Database\OTF(['database' => $dbname]);
                            $db     = DB::connection($dbname);

                            // update contact details
                            $owner = Owner::on($database)->insert(array(
                                'strIDNumber' => $owner_details->strIDNumber
                                , 'NAME' => $owner_details->NAME
                                , 'TITLE' => $owner_details->TITLE
                                , 'INITIALS' => $owner_details->INITIALS
                                , 'strSurname' => $owner_details->strSurname
                                , 'strFirstName' => $owner_details->strFirstName
                                , 'strHomePhoneNo' => $owner_details->strHomePhoneNo
                                , 'strWorkPhoneNo' => $owner_details->strWorkPhoneNo
                                , 'strCellPhoneNo' => $owner_details->strCellPhoneNo
                                , 'EMAIL' => $owner_details->EMAIL
                                , 'created_at' => $now
                                , 'updated_at' => $now));

                            // update from prop rec
                        } else {
                            $dbname = $database;
                            $otf    = new \App\Database\OTF(['database' => $dbname]);
                            $db     = DB::connection($dbname);

                            $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA[$x]['strIdentity'], 'NAME' => $updatesA[$x]['strOwners'], 'created_at' => $now));
                        }

                    } else {
                        // dont wipe old details

                    }
                    // add note
                    //
                    $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA[$x]['strKey'])->get();
                    if ($hasnote->count() == 0) {
                        //$note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => "\n" . $now . '  ' . $updatesA[$x]['strOwners'] . '  - New Owner.', 'created_at' => $now));
                        $note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'created_at' => $now));
                        $lastupdate = $updatesA[$x]['strKey'];
                    } else {
                        // only add note addendum once for the strKey - lastupdate
                        //
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
                //
                $delp = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

                // delete them
                //
                Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->delete();

                // report
                //
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

        $message = 'Update completed.';
        Session::flash('flash_type', 'alert-success');
        return Redirect::back()->with('flash_message', $message);

    }

    // ST Update
    //
    public function updateST(Request $request)
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

        if (strpos($fileinfo['filename'], 'ST') !== false) {
            $type  = "ST";
            $valid = 1;
        }
        if (strpos($fileinfo['filename'], 'FH') !== false) {
            $type  = "FH";
            $valid = 0;
        }

        // if not FH or ST the return error
        //
        if ($valid !== 1) {
            $message = 'This is not a valid ST update file.';
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
        //
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
        //
        if (!$csv_updater->update($csv_file, $database)) {
            $message = 'Error importing the file during update.';
            Session::flash('flash_type', 'alert-danger');
            return Redirect::back()->with('flash_message', $message);
        }

        // connect to the database
        //
        $dbname = $database;
        $otf    = new \App\Database\OTF(['database' => $dbname]);
        $db     = DB::connection($dbname);

        // totals
        $del        = 0;
        $add        = 0;
        $tot        = 0;
        $lastupdate = "none";

        // time
        $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // setup log file - same name as csv with .log ext
        $destination_directory = storage_path('updates/tmp');
        $original_file_name    = $csv_file->getClientOriginalName();
        $logfilename           = $destination_directory . '/' . $original_file_name;
        $logfilename           = str_replace('.csv', '.log', $logfilename);

        // File::put($logfilename, '');

        // setup log file - same name as csv with .log ext
        $destination_directory = storage_path('updates/tmp');
        $original_file_name    = $csv_file->getClientOriginalName();
        $this->logfilename     = $destination_directory . '/' . $original_file_name;
        $this->logfilename     = str_replace('.csv', '.log', $logfilename);

        File::put($this->logfilename, '');

        //dd($logfilename);

        // check latest regDate in properties and delete old updates
        $propDate = Property::on($database)->orderBy('dtmRegDate', 'desc')->first();

        Update::on($database)->where('dtmRegDate', '<=', $propDate['dtmRegDate'])->delete();

        $echo = "last dtmRegDate in properties = " . $propDate['dtmRegDate'];
        File::append($this->logfilename, $echo . "\r\n");

        // get all update records
        //
        $updates = Update::on($database)->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->orderBy('strKey')->orderBy('strTitleDeed')->get();

        // convert to array
        //
        $updatesA = $updates->toArray();

        $echo = 'updates to process = ' . $updates->count();
        File::append($this->logfilename, $echo . "\r\n");

        // process update records
        //
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // check if there is an exact match on strKey
            //  $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

            $units     = $this->noOfUnits($updatesA[$x]['strComplexNo']);
            $arr_units = $this->arrOfUnits($updatesA[$x]['strComplexNo']);

            $echo = '...........................................................................................................................................';
            File::append($this->logfilename, $echo . "\r\n");
            $echo = $updatesA[$x]['strKey'] . " - " . $updatesA[$x]['dtmRegDate'];
            File::append($this->logfilename, $echo . "\r\n");
            $echo = $units . ' units  -> ' . implode(" ", $arr_units);
            File::append($this->logfilename, $echo . "\r\n");
            $echo = '...........................................................................................................................................';
            File::append($this->logfilename, $echo . "\r\n");

            // fetch all property records for the complex where the reg date is older than the update
            $properties = Property::on($database)->orderBy('strKey')->where('strComplexName', $updatesA[$x]['strComplexName'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

            // check if there is an exact match on strKey with an earlier date
            $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

            // if the strKey exists
            if ($exact->count() > 0) {

                $echo = ' - ' . $exact->count() . ' matching strKey in properties';
                File::append($this->logfilename, $echo . "\r\n");

                // check the updates are newer
                $anyupdates = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

                // if record already exists dont add it
                $dualowner = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where('strIdentity', '=', $updatesA[$x]['strIdentity'])->where('strTitleDeed', '=', $updatesA[$x]['strTitleDeed'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '=', Date($updatesA[$x]['dtmRegDate']))->get();

                // echo 'anyupdates - ' . $anyupdates->count() . ' dualowner - ' . $dualowner->count() . '<br>';

                // check it is a new update and that it is not already in the database
                if ($anyupdates->count() > 0 && $dualowner->count() == 0) {

                    $echo = ' - ' . $anyupdates->count() . $updatesA[$x]['strKey'] . '  update ------------ exact key';
                    File::append($this->logfilename, $echo . "\r\n");

                    // if single unit
                    if ($units == 1) {

                        // dd($arr_units[0], $arr_units);
                        $this->newUnit($updatesA[$x], $arr_units[0], $database);
                    } else {

                        File::append($this->logfilename, $echo . "\r\n");
                        $this->newMultiUnit($updatesA[$x], $arr_units, $database);
                    }

                } else {
                    //echo ' - <b>' . $anyupdates->count() . $updatesA[$x]['strKey'] . '</b>-----------  NO updates' . "<br>";
                    $echo = ' No updates';
                    File::append($this->logfilename, $echo . "\r\n");
                }
                // strKey not found - search by units as may be in another key
            } else {
                $echo = 'No match for strKey in properties - search by unit';
                File::append($this->logfilename, $echo . "\r\n");

                $isFound = 0;
                for ($u = 0; $u < $units; $u++) {

                    $echo = " - Unit  " . ($u + 1) . " - " . $arr_units[$u];
                    File::append($this->logfilename, $echo . "\r\n");
                    $echo = " ---------------";
                    File::append($this->logfilename, $echo . "\r\n");

                    $isFound = $this->findUnit($updatesA[$x]['strComplexName'], $arr_units[$u], $properties, $updatesA[$x]['dtmRegDate'], $updatesA[$x]['strSellers'], $updatesA[$x]['strIdentity'], $database, $updatesA[$x]);

                    // properties could have been updated so fetch again
                    // fetch all property records for the complex where the reg date is older than the update
                    $properties = Property::on($database)->orderBy('strKey')->where('strComplexName', $updatesA[$x]['strComplexName'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

                }

                // below is done in findunit so not needed here
                if (!$isFound) {
                    // echo " -- add this record as it is not found anywhere so must be a newcomer <br>";
                }

            }

        }

        // dd end for debug so I can read log

        //dd("update completed - delete this dd()");
        $echo = '';
        File::append($this->logfilename, $echo . "\r\n");
        $echo = '...........................................................................';
        File::append($this->logfilename, $echo . "\r\n");

        $echo = 'update compleded successfully';
        File::append($this->logfilename, $echo . "\r\n");

        $message = 'Update completed.';
        Session::flash('flash_type', 'alert-success');
        return Redirect::back()->with('flash_message', $message);

    }

    /**
     *  get number of units in strKey
     *
     * @return int
     */
    public function noOfUnits($strKey)
    {
        if (strpos($strKey, "&") > -1) {
            $arr = explode(' & ', $strKey);
        } else {

            $arr = explode(' ', $strKey);
        }

        return (sizeof($arr));
    }

    /**
     *  get array of units
     *
     * @return int
     */
    public function arrOfUnits($strComplexNo)
    {

        if (strpos($strComplexNo, "&") > -1) {
            $arr = explode('&', $strComplexNo);
        } else {
            $arr = explode(' ', $strComplexNo);

        }

        for ($p = 0; $p < sizeof($arr); $p++) {
            $arr[$p] = ltrim(rtrim($arr[$p]));
        }

        return ($arr);
    }

    /**
     *  see if update exists in properties
     *
     * pass complexname , unit , properties array , updated date , sellers
     *
     *
     * @return properties record
     */
    public function findUnit($strComplexName, $unit, $properties, $updateDate, $sellers, $sellerId, $database, $updatesA)
    {

        $unit = ltrim(rtrim($unit));

        $num = 1;

        $userCheck = 0;

        for ($p = 0; $p < $properties->count(); $p++) {

            $punits = $this->arrOfUnits($properties[$p]['strComplexNo']);

            //  dd(strpos($properties[$p]['strComplexNo'], "&"), $properties[$p]['strComplexNo']);

            $found = in_array($unit, $punits);

            //dd($unit, $punits, $found, $properties);
            // echo $properties[$p]['strKey'] . " " . $properties[$p]['dtmRegDate'] . "<br><br> unit " . $unit . " punits " . implode(' ', $punits) . "  found - " . $found . "<br>";

            if ($found) {
                $num = $num + 1;

                $echo = "- Found Unit  - " . $properties[$p]['strKey'] . " " . $properties[$p]['dtmRegDate'];
                File::append($this->logfilename, $echo . "\r\n");

                // echo " unit " . $unit . " punits " . implode(' ', $punits) . "  found - " . $found . "<br>";
                //    $isregdateolder = $updateDate ;

                $dateStart = Carbon::createFromFormat('Y-m-d', $updateDate);
                $dateEnd   = Carbon::createFromFormat('Y-m-d', $properties[$p]['dtmRegDate']);

                $diffInDays = $dateStart->diffInDays($dateEnd, false);

                // regdate is older than update
                $isdateolder = 0;

                if ($diffInDays < 0) {
                    $checkdate   = " Reg Date is Older than the update";
                    $isdateolder = 1;
                } elseif ($diffInDays == 0) {
                    $checkdate = "Reg Date is the same";
                } else {
                    $checkdate = "   Reg Date is more recent than the update";
                }
                // echo "---- found - " . $found . "<br>";
                $echo = "----" . $checkdate;
                File::append($this->logfilename, $echo . "\r\n");

                //  echo "update date" . $updateDate . "<br>";
                //   echo "porperties date" . $properties[$p]['dtmRegDate'] . "<br>";
                // echo "----" . $num . "--------- " . $properties[$p]['strOwners'] . "    |    " . $properties[$p]['dtmRegDate'] . " - <b> " . $checkdate . "</b><br>";
                //   echo "----" . $num - 1 . "--------- " . $properties[$p]['strKey'] . " - " . $properties[$p]['dtmRegDate'] . "<br>";

// update as older
                if ($isdateolder == 1) {
                    // remove unit from old key
                    $newKey = $this->removeUnit($unit, $properties[$p]['strComplexNo'], $properties[$p]['strComplexName']);
                    if ($newKey == " DELETE ") {
                        $echo = "----" . $num - 1 . "--------- " . $properties[$p]['strKey'] . "  delete this old key";
                        File::append($this->logfilename, $echo . "\r\n");
                    } else {
                        $echo = "----" . $num - 1 . "--------- " . $newKey . " <- updated old key";
                        File::append($this->logfilename, $echo . "\r\n");
                    }

                    // check if the owner already has a unit in this complex
                    $check = Property::on($database)->where('strIdentity', $sellerId)->where('strComplexName', $strComplexName)->get();

                    if ($check->count() > 0) {
                        $echo = "----" . $num - 1 . "--------- " . $check[0]['strKey'] . " & " . $unit . " <- updated new key    user already has a unit in this complex - add it " . $sellerId;
                        File::append($this->logfilename, $echo . "\r\n");
                        $this->AddRemoveUnit($database, $properties[$p], $updatesA, $unit, $check);
                        ///                     // $this->ADDunit($updatesA, $unit, $database, $check[0], $properties[$p]);

                    } else {
                        $echo = "----" . $num - 1 . "--------- " . $strComplexName . " " . $unit . " <- add new key   ";
                        File::append($this->logfilename, $echo . "\r\n");
                        // send old prop
                        // new prop
                        // new unit
                        $this->AddRemoveUnit($database, $properties[$p], $updatesA, $unit, $check);
                    }
                } else {
                    $echo = " No update ";
                    File::append($this->logfilename, $echo . "\r\n");
                }
                // if found exit looping through properties
                //$p = $properties->count();
            } else {

            }

        }

        if ($num == 1) {

            $echo = "- " . "No match for this unit";
            File::append($this->logfilename, $echo . "\r\n");

            // check if the owner already has a unit in this complex
            $check = Property::on($database)->where('strIdentity', $sellerId)->where('strComplexName', $strComplexName)->get();

            if ($check->count() > 0) {
                $echo = "- " . $num . " " . $check[0]['strKey'] . " user already has a unit in this complex  " . $sellerId . "   " . $check[0]['dtmRegDate'];
                File::append($this->logfilename, $echo . "\r\n");

//////   working on this
                $this->ADDunit($updatesA, $unit, $database, $check[0], 'nomatch');

            } else {

                //  this unit is not in properties
                //  the owner owns no units in the complex
                //  ADD

                $echo = "----" . $num . "owner has no units in this complex " . " <- add new key    ";
                File::append($this->logfilename, $echo . "\r\n");

                $this->newUnit($updatesA, $unit, $database);

            }

            // must be a new record - no match found
            return false;
        }
        // found a match
        return true;
    }

    /**
     *  remove unit from key
     *
     * @return string new key
     */
    public function removeUnit($unit, $strComplexNo, $complex)
    {

        $unit = ltrim(rtrim($unit));

        $a = explode(" & ", $strComplexNo);

        if (($key = array_search($unit, $a)) !== false) {
            unset($a[$key]);
        }

        $a = implode(" & ", $a);

        if (strlen($a) == 0) {
            $a = " DELETE ";
        } else {
            $a = $complex . ' ' . $a;
        }
        // dd($a, sizeof($a));

        return $a;
    }

    public function removeComplexNoUnit($unit, $strComplexNo)
    {

        $unit = ltrim(rtrim($unit));

        $a = explode(" & ", $strComplexNo);

        if (($key = array_search($unit, $a)) !== false) {
            unset($a[$key]);
        }

        $a = implode(" & ", $a);

        if (strlen($a) == 0) {
            $a = " DELETE ";
        } else {

        }
        // dd($a, sizeof($a));

        return $a;
    }

    public function sortUnits($strComplexNo)
    {

        $a = explode(" & ", $strComplexNo);

        sort($a);

        $a = implode(" & ", $a);

        return $a;
    }

    public function newUnit($updatesA, $unit, $database)
    {

        $echo = "Entering newUnit ........................................ ";
        File::append($this->logfilename, $echo . "\r\n");

        //dd($updatesA, $unit);
        // check if multiple units
        // get sqM for specific unit
        $a     = explode(" & ", $updatesA['strComplexNo']);
        $sqM   = explode(" & ", $updatesA['strSqMeters']);
        $found = in_array($unit, $a);
        if ($found) {
            $place                   = array_search($unit, $a);
            $updatesA['strSqMeters'] = $sqM[$place];
        }

        $now            = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
        $updatesA['id'] = null;

        $updatesA['strKey']      = $updatesA['strComplexName'] . " " . $unit;
        $updatesA['numStreetNo'] = $updatesA['strStreetNo'];

        $updatesA['numComplexNo'] = $unit;
        $updatesA['strComplexNo'] = $unit;
        $updatesA['created_at']   = $now;

        // dd($updatesA, $a, $found, $place, $a[$place], $sqM[$place]);

        // delete older ones
        $delOld = Property::on($database)->where('strKey', $updatesA['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA['dtmRegDate']))->delete();

        $echo = "..DELETE OLD ............    " . $updatesA['strKey'] . "   -   " . $delOld;
        File::append($this->logfilename, $echo . "\r\n");

        // insert new property record
        Property::on($database)->insert($updatesA);

        // add owner record if not existing
        //
        $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA['strIdentity']);
        if ($hascontact->count() == 0) {

            // update details from admin contacts
            $admin_contacts = "farmbook_admin";
            $otf            = new \App\Database\OTF(['database' => $admin_contacts]);
            $db             = DB::connection($admin_contacts);

            $owner_details = Contact::on($admin_contacts)->select('*')->where('strIDNumber', $updatesA['strIdentity'])->first();

            // contact details found in admin contacts
            if (sizeof($owner_details) == 1) {
                $uid = $owner_details->strIDNumber;

                // set database back
                $dbname = $database;
                $otf    = new \App\Database\OTF(['database' => $dbname]);
                $db     = DB::connection($dbname);

                // update contact details
                $owner = Owner::on($database)->insert(array(
                    'strIDNumber' => $owner_details->strIDNumber
                    , 'NAME' => $owner_details->NAME
                    , 'TITLE' => $owner_details->TITLE
                    , 'INITIALS' => $owner_details->INITIALS
                    , 'strSurname' => $owner_details->strSurname
                    , 'strFirstName' => $owner_details->strFirstName
                    , 'strHomePhoneNo' => $owner_details->strHomePhoneNo
                    , 'strWorkPhoneNo' => $owner_details->strWorkPhoneNo
                    , 'strCellPhoneNo' => $owner_details->strCellPhoneNo
                    , 'EMAIL' => $owner_details->EMAIL
                    , 'created_at' => $now
                    , 'updated_at' => $now));

                // update from prop rec
            } else {
                $dbname = $database;
                $otf    = new \App\Database\OTF(['database' => $dbname]);
                $db     = DB::connection($dbname);

                $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA['strIdentity'], 'NAME' => $updatesA['strOwners'], 'created_at' => $now));
            }

        } else {
            // dont wipe old details

        }

        // add note
        //
        $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA['strKey'])->get();
        if ($hasnote->count() == 0) {
            //$note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => "\n" . $now . '  ' . $updatesA[$x]['strOwners'] . '  - New Owner.', 'created_at' => $now));
            $note = Note::on($database)->insert(array('strKey' => $updatesA['strKey'], 'numErf' => $updatesA['numErf'], 'created_at' => $now));
        } else {

            $note = Note::on($database)->where('strKey', '=', $updatesA['strKey'])->update(array('memNotes' => DB::raw('concat(memNotes, " \n' . $now . '  ' . $updatesA['strOwners'] . ' -  New Owner.")'), 'updated_at' => $now));

        }

        $echo = "Finishing newUnit ........................................  ";
        File::append($this->logfilename, $echo . "\r\n");
    }

    public function ADDUnit($updatesA, $unit, $database, $ownerRecord, $oldkey)
    {

        $echo = "Entering ADDUnit ........................................ ";
        File::append($this->logfilename, $echo . "\r\n");

        if ($oldkey != 'nomatch') {
            // echo " remove unit from old key " . $oldkey['strKey'] . "<br>";
        } else {
            // echo "- <b>No old key just add unit</b><br>";
        }

        // get old key for notes change
        $oldNoteKey = $ownerRecord['strKey'];

        // check unit is not already in the key
        $existingUnits = $this->arrOfUnits($ownerRecord['strComplexNo']);

        $test = in_array($unit, $existingUnits);
        $dual = false;

        // check if possible dual owner
        if ($ownerRecord['strIdentity'] != $updatesA['strIdentity'] && $ownerRecord['strTitleDeed'] == $updatesA['strTitleDeed'] && $ownerRecord['dtmRegDate'] == $updatesA['dtmRegDate']) {

            $dual = true;
            $echo = "     **  DualOwner ";
            File::append($this->logfilename, $echo . "\r\n");
        }

        //unit is in key dont add unless it is a dual owner
        if ($test && !$dual) {

            $echo = "     **   unit already in key " . $ownerRecord['strComplexNo'] . " - unit " . $unit . " - " . $test;
            File::append($this->logfilename, $echo . "\r\n");
            $echo = "     **   Not dualOwner " . $ownerRecord['strIdentity'] . " - " . $updatesA['strIdentity'] . " - " . $ownerRecord['strTitleDeed'] . " - " . $updatesA['strTitleDeed'];
            File::append($this->logfilename, $echo . "\r\n");
            // add key
        } else {

            // dd($updatesA, $unit, $database, $ownerRecord);

            // get sq meters for the unit
            $a     = explode(" & ", $updatesA['strComplexNo']);
            $sqM   = explode(" & ", $updatesA['strSqMeters']);
            $found = in_array($unit, $a);
            if ($found) {
                $place                   = array_search($unit, $a);
                $updatesA['strSqMeters'] = $sqM[$place];
            }

            $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

            unset($updatesA['id']);

            $updatesA['strKey']       = $ownerRecord['strKey'] . ' & ' . $unit;
            $updatesA['numStreetNo']  = $updatesA['strStreetNo'];
            $updatesA['strComplexNo'] = $ownerRecord['strComplexNo'] . ' & ' . $unit;
            $updatesA['numComplexNo'] = $updatesA['strComplexNo'];
            $updatesA['strSqMeters']  = $ownerRecord['strSqMeters'] . ' & ' . $updatesA['strSqMeters'];
            $updatesA['created_at']   = $now;
            $updatesA['updated_at']   = $now;

            $echo = "     Sorted key ----" . $this->sortUnits($updatesA['strComplexNo']);
            File::append($this->logfilename, $echo . "\r\n");

            // update record
            Property::on($database)->where('id', $ownerRecord['id'])->update($updatesA);

            // delete older records
            //Property::on($database)->where(

            // of course owner exists so no update needed

            // remove old units

            // update old note
            //
            $hasnote = Note::on($database)->select('id')->where('strKey', '=', $oldNoteKey)->get();
            if ($hasnote->count() == 0) {
                //$note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => "\n" . $now . '  ' . $updatesA[$x]['strOwners'] . '  - New Owner.', 'created_at' => $now));
                //$note = Note::on($database)->insert(array('strKey' => $updatesA['strKey'], 'numErf' => $updatesA['numErf'], 'created_at' => $now));
            } else {

                $note = Note::on($database)->where('strKey', '=', $oldNoteKey)->update(array('strKey' => $updatesA['strKey'], 'memNotes' => DB::raw('concat(memNotes, " \n' . $now . '  ' . $unit . ' -  Unit Added.")'), 'updated_at' => $now));

            }

        }

        $echo = "Finishing ADDUnit ........................................  ";
        File::append($this->logfilename, $echo . "\r\n");
    }

    //add remove unit
    // $database - database farmbook
    // $oldprop - the property found that has the update unit
    // $updateA - the update record
    // $unit - unit updating
    // $pastowner - the update has a unit in the complex
    public function AddRemoveUnit($database, $oldprop, $updatesA, $unit, $pastowner)
    {

        $echo = "Entering AddRemoveUnit ........................................ ";
        File::append($this->logfilename, $echo . "\r\n");
        //   $sortunits = $this->sortUnits("10 & 40 & 50 & 1");
        //   echo $sortunits;
        //   dd($oldprop['strKey'], $newprop['strKey'], $unit);
        //  echo "ADD REMOVE UNIT   >>>>>" . "<br>";
        //  echo "old key " . $oldprop['strKey'] . " - id = " . $oldprop['id'] . "<br>";
        //  echo "unit " . $unit . "sq " . $updatesA['strSqMeters'] . "<br>";
        $oldkey = $oldprop['strKey'];
        $newkey = $this->removeUnit($unit, $oldprop['strComplexNo'], $oldprop['strComplexName']);
        //  echo "update with " . $newkey . "<br>";
        //  echo "new " . $updatesA['strKey'] . "<br>";
        //  echo "count  " . $oldprop->count() . "<br>";
        //  echo "old prop strKey  " . $oldprop['strKey'] . "<br>";
        //  echo "already has unit  " . $pastowner[0]['strKey'] . "<br>";

        $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // echo "     Sorted key ----" . $this->sortUnits($updatesA['strComplexNo']) . "<br>";

        // Properties - remove unit from old key and update
        // check if dual owner and process each record
        $dual = Property::on($database)->where('strKey', $oldkey)->get();

        // echo "check dual owner - count must be > 1   = " . $dual->count() . "   " . $oldkey . "<br>";

        // update old key and remove unit
        for ($r = 0; $r < $dual->count(); $r++) {
            //  echo "  owners - " . $r . "  " . $dual[$r]['strIdentity'] . "    " . $dual[$r]['strOwners'] . "    " . $dual[$r]['strKey'] . "<br>";

            unset($oldprop['id']);

            $dual[$r]['strKey'] = $newkey;
            //  $updatesA['numStreetNo']  = $updatesA['strStreetNo'];
            $dual[$r]['strComplexNo'] = $this->removeComplexNoUnit($updatesA['strComplexNo'], $dual[$r]['strComplexNo']);
            $dual[$r]['numComplexNo'] = $dual[$r]['strComplexNo'];
            $dual[$r]['strSqMeters']  = $this->removeComplexNoUnit($updatesA['strSqMeters'], $dual[$r]['strSqMeters']);

            // update regdate
            $dual[$r]['dtmRegDate'] = $updatesA['dtmRegDate'];

            // $pastowner[0]['created_at']   = $now;
            $dual[$r]['updated_at'] = $now;
            $updateRecord           = $dual[$r]->toArray();
            $echo                   = " - updating " . $newkey . "   removed unit " . $unit . "   " . $dual[$r]['strIdentity'] . "   " . $dual[$r]['dtmRegDate'];
            File::append($this->logfilename, $echo . "\r\n");
            Property::on($database)->where('id', $dual[$r]['id'])->update($updateRecord);

            // Notes - remove unit from old key and update
            //       - add note that unit has been removed
            $note = Note::on($database)->where('strKey', '=', $oldkey)->update(array('strKey' => $newkey, 'memNotes' => DB::raw('concat(memNotes, " \n' . $now . '  ' . $unit . ' -  Unit Removed.")'), 'updated_at' => $now));

        }

        //  check if owner owns a unit in this complex
        $hasunit = 0;

        if (!$pastowner->isEmpty()) {
            $new  = Property::on($database)->where('strIdentity', $pastowner[0]['strIdentity'])->where('strComplexName', $updatesA['strComplexName'])->get();
            $echo = "Past owners found" . $new->count();
            File::append($this->logfilename, $echo . "\r\n");
            $hasunit = 1;
        }

        // owner already owns a unit in the complex
        if ($hasunit == 1) {
            // owner has another unit in complex
            // Properties - add unit to owners existing units and update
            // Notes - update notes with new key
            //         - add note that unit added

            unset($updatesA['id']);

            $updatesA['strKey']       = $new[0]['strKey'] . ' & ' . $unit;
            $updatesA['numStreetNo']  = $updatesA['strStreetNo'];
            $updatesA['strComplexNo'] = $new[0]['strComplexNo'] . ' & ' . $unit;
            $updatesA['numComplexNo'] = $updatesA['strComplexNo'];
            $updatesA['strSqMeters']  = $new[0]['strSqMeters'] . ' & ' . $updatesA['strSqMeters'];
            $updatesA['created_at']   = $now;
            $updatesA['updated_at']   = $now;

            $echo = "     New key added  key ----" . $this->sortUnits($updatesA['strComplexNo']);
            File::append($this->logfilename, $echo . "\r\n");

            // update record
            Property::on($database)->where('id', $new[0]['id'])->update($updatesA);

            for ($o = 0; $o < $new->count(); $o++) {

                $no        = $new[$o]['strComplexNo'] . " & " . $unit;
                $sortunits = $this->sortUnits($no);
                $echo      = "old key   " . $new[$o]['strKey'] . " " . $new[$o]['strIdentity'];
                File::append($this->logfilename, $echo . "\r\n");
                $echo = "sorted new key   " . $updatesA['strComplexName'] . " " . $sortunits . "  no owners " . $pastowner->count();
                File::append($this->logfilename, $echo . "\r\n");
            }
        } else {
            // owner has no other units in complex
            // Properties - new unit
            // Notes - new note
            $echo = ' - added new Key - ' . $updatesA['strKey'];
            File::append($this->logfilename, $echo . "\r\n");
            unset($updatesA['id']);
            $updatesA['numStreetNo']  = $updatesA['strStreetNo'];
            $updatesA['numComplexNo'] = $updatesA['strComplexNo'];
            $updatesA['created_at']   = $now;
            $updatesA['updated_at']   = $now;
            Property::on($database)->insert($updatesA);

            $note = Note::on($database)->insert(array('strKey' => $updatesA['strKey'], 'numErf' => $updatesA['numErf'], 'created_at' => $now));

            // add owner rec
            $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA['strIdentity']);
            if ($hascontact->count() == 0) {

                // update details from admin contacts
                $admin_contacts = "farmbook_admin";
                $otf            = new \App\Database\OTF(['database' => $admin_contacts]);
                $db             = DB::connection($admin_contacts);

                $owner_details = Contact::on($admin_contacts)->select('*')->where('strIDNumber', $updatesA['strIdentity'])->first();

                // contact details found in admin contacts
                if (sizeof($owner_details) == 1) {
                    $uid = $owner_details->strIDNumber;

                    // set database back
                    $dbname = $database;
                    $otf    = new \App\Database\OTF(['database' => $dbname]);
                    $db     = DB::connection($dbname);

                    // update contact details
                    $owner = Owner::on($database)->insert(array(
                        'strIDNumber' => $owner_details->strIDNumber
                        , 'NAME' => $owner_details->NAME
                        , 'TITLE' => $owner_details->TITLE
                        , 'INITIALS' => $owner_details->INITIALS
                        , 'strSurname' => $owner_details->strSurname
                        , 'strFirstName' => $owner_details->strFirstName
                        , 'strHomePhoneNo' => $owner_details->strHomePhoneNo
                        , 'strWorkPhoneNo' => $owner_details->strWorkPhoneNo
                        , 'strCellPhoneNo' => $owner_details->strCellPhoneNo
                        , 'EMAIL' => $owner_details->EMAIL
                        , 'created_at' => $now
                        , 'updated_at' => $now));

                    // update from prop rec
                } else {
                    $dbname = $database;
                    $otf    = new \App\Database\OTF(['database' => $dbname]);
                    $db     = DB::connection($dbname);

                    $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA['strIdentity'], 'NAME' => $updatesA['strOwners'], 'created_at' => $now));
                }

            } else {
                // dont wipe old details

            }

        }

        $echo = "Finishing AddRemoveUnit ........................................ ";
        File::append($this->logfilename, $echo . "\r\n");
    }

    public function newMultiUnit($updatesA, $unit, $database)
    {

        $echo = "Entering newMultiUnit ........................................ ";
        File::append($this->logfilename, $echo . "\r\n");

        //dd($updatesA, $unit);
        // check if multiple units
        // get sqM for specific unit
        $a     = explode(" & ", $updatesA['strComplexNo']);
        $sqM   = explode(" & ", $updatesA['strSqMeters']);
        $found = in_array($unit, $a);
        if ($found) {
            $place                   = array_search($unit, $a);
            $updatesA['strSqMeters'] = $sqM[$place];
        }

        $now            = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
        $updatesA['id'] = null;

        $updatesA['strKey']      = $updatesA['strKey'];
        $updatesA['numStreetNo'] = $updatesA['strStreetNo'];

        $updatesA['numComplexNo'] = $updatesA['strComplexNo'];

        $updatesA['created_at'] = $now;

        // dd($updatesA, $a, $found, $place, $a[$place], $sqM[$place]);

        // delete older ones
        $delOld = Property::on($database)->where('strKey', $updatesA['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA['dtmRegDate']))->delete();

        $echo = "..DELETE OLD ............    " . $updatesA['strKey'] . "   -   " . $delOld;
        File::append($this->logfilename, $echo . "\r\n");

        // insert new property record
        Property::on($database)->insert($updatesA);

        // add owner record if not existing
        //
        $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA['strIdentity']);
        if ($hascontact->count() == 0) {

            // update details from admin contacts
            $admin_contacts = "farmbook_admin";
            $otf            = new \App\Database\OTF(['database' => $admin_contacts]);
            $db             = DB::connection($admin_contacts);

            $owner_details = Contact::on($admin_contacts)->select('*')->where('strIDNumber', $updatesA['strIdentity'])->first();

            // contact details found in admin contacts
            if (sizeof($owner_details) == 1) {
                $uid = $owner_details->strIDNumber;

                // set database back
                $dbname = $database;
                $otf    = new \App\Database\OTF(['database' => $dbname]);
                $db     = DB::connection($dbname);

                // update contact details
                $owner = Owner::on($database)->insert(array(
                    'strIDNumber' => $owner_details->strIDNumber
                    , 'NAME' => $owner_details->NAME
                    , 'TITLE' => $owner_details->TITLE
                    , 'INITIALS' => $owner_details->INITIALS
                    , 'strSurname' => $owner_details->strSurname
                    , 'strFirstName' => $owner_details->strFirstName
                    , 'strHomePhoneNo' => $owner_details->strHomePhoneNo
                    , 'strWorkPhoneNo' => $owner_details->strWorkPhoneNo
                    , 'strCellPhoneNo' => $owner_details->strCellPhoneNo
                    , 'EMAIL' => $owner_details->EMAIL
                    , 'created_at' => $now
                    , 'updated_at' => $now));

                // update from prop rec
            } else {
                $dbname = $database;
                $otf    = new \App\Database\OTF(['database' => $dbname]);
                $db     = DB::connection($dbname);

                $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA['strIdentity'], 'NAME' => $updatesA['strOwners'], 'created_at' => $now));
            }

        } else {
            // dont wipe old details

        }

        // add note
        //
        $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA['strKey'])->get();
        if ($hasnote->count() == 0) {
            //$note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => "\n" . $now . '  ' . $updatesA[$x]['strOwners'] . '  - New Owner.', 'created_at' => $now));
            $note = Note::on($database)->insert(array('strKey' => $updatesA['strKey'], 'numErf' => $updatesA['numErf'], 'created_at' => $now));
        } else {

            $note = Note::on($database)->where('strKey', '=', $updatesA['strKey'])->update(array('memNotes' => DB::raw('concat(memNotes, " \n' . $now . '  ' . $updatesA['strOwners'] . ' -  New Owner.")'), 'updated_at' => $now));

        }

        $echo = "Finishing newMultiUnit ........................................  ";
        File::append($this->logfilename, $echo . "\r\n");
    }

} // controller end
