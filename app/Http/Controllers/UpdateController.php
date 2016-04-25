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

        // get all update records
        //
        $updates = Update::on($database)->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->orderBy('strKey')->orderBy('strTitleDeed')->get();

        // convert to array
        //
        $updatesA = $updates->toArray();

        echo 'updates to process = <b>' . $updates->count() . "</b><br>";

        // process update records
        //
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // check if there is an exact match on strKey
            //  $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

            $units     = $this->noOfUnits($updatesA[$x]['strComplexNo']);
            $arr_units = $this->arrOfUnits($updatesA[$x]['strComplexNo']);
            echo '...........................................................................................................................................' . "<br>";
            echo $updatesA[$x]['strKey'] . " - " . $updatesA[$x]['dtmRegDate'] . "<br>";
            echo $units . ' units  -> ' . implode(" ", $arr_units) . "<br>";

            // fetch all property records for the complex where the reg date is older than the update
            $properties = Property::on($database)->orderBy('strKey')->where('strComplexName', $updatesA[$x]['strComplexName'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

            // check if there is an exact match on strKey with an earlier date
            $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->get();

            // if the strKey exists
            if ($exact->count() > 0) {

                echo ' - <b>' . $exact->count() . '</b>  EXACT key is in properties' . "<br>";

                // check the updates are newer
                $anyupdates = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();

                // if record already exists dont add it
                $dualowner = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where('strIdentity', '=', $updatesA[$x]['strIdentity'])->where('strTitleDeed', '=', $updatesA[$x]['strTitleDeed'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '=', Date($updatesA[$x]['dtmRegDate']))->get();

                echo 'anyupdates - ' . $anyupdates->count() . ' dualowner - ' . $dualowner->count() . '<br>';

                // check it is a new update and that it is not already in the database
                if ($anyupdates->count() > 0 && $dualowner->count() == 0) {
                    echo ' - <b>' . $anyupdates->count() . $updatesA[$x]['strKey'] . '</b>  update ------------ exact key' . "<br>";

                    // if single unit
                    if (sizeof($units == 1)) {

                        // dd($arr_units[0], $arr_units);
                        $this->newUnit($updatesA[$x], $arr_units[0], $database);
                    } else {

                        echo " **** multiple unit UPDATE - still to do <br>";
                    }

                } else {
                    echo ' - <b>' . $anyupdates->count() . $updatesA[$x]['strKey'] . '</b>-----------  NO updates' . "<br>";
                }
                // strKey not found - search by units as may be in another key
            } else {
                echo ' - <b>' . $exact->count() . '</b>  EXACT key is NOT in properties - search by unit' . "<br>";
                $isFound = 0;
                for ($u = 0; $u < $units; $u++) {

                    echo " -- Unit" . ($u + 1) . " - <b>" . $arr_units[$u] . "</b> <br>";

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

            echo "<br>";
        }

        // dd end for debug so I can read log

        dd();

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
            //echo $properties[$p]['strKey'] . " " . $properties[$p]['dtmRegDate'] . "unit " . $unit . " punits " . implode(' ', $punits) . "  found - " . $found . "<br>";

            if ($found) {
                $num = $num + 1;
                //    $isregdateolder = $updateDate ;

                $dateStart = Carbon::createFromFormat('Y-m-d', $updateDate);
                $dateEnd   = Carbon::createFromFormat('Y-m-d', $properties[$p]['dtmRegDate']);

                $diffInDays = $dateStart->diffInDays($dateEnd, false);

                // regdate is older than update
                $isdateolder = 0;

                if ($diffInDays < 0) {
                    $checkdate   = "&#10003" . " Reg Date is Older than the update";
                    $isdateolder = 1;
                } elseif ($diffInDays == 0) {
                    $checkdate = "&#10060" . "Reg Date is the same";
                } else {
                    $checkdate = "&#10060" . "   Reg Date is more recent than the update";
                }
                echo "---- found - " . $found . "<br>";
                echo "----" . $checkdate . "<br>";
                // echo "----" . $num . "--------- " . $properties[$p]['strOwners'] . "    |    " . $properties[$p]['dtmRegDate'] . " - <b> " . $checkdate . "</b><br>";
                echo "----" . $num - 1 . "--------- " . $properties[$p]['strKey'] . " - " . $properties[$p]['dtmRegDate'] . "<br>";

// update as older
                if ($isdateolder == 1) {
                    // remove unit from old key
                    $newKey = $this->removeUnit($unit, $properties[$p]['strComplexNo'], $properties[$p]['strComplexName']);
                    if ($newKey == " DELETE ") {
                        echo "----" . $num - 1 . "--------- <b>" . $properties[$p]['strKey'] . "  delete this old key" . "</b><br>";
                    } else {
                        echo "----" . $num - 1 . "--------- <b>" . $newKey . " <- updated old key</b><br>";
                    }

                    // check if the owner already has a unit in this complex
                    $check = Property::on($database)->where('strIdentity', $sellerId)->where('strComplexName', $strComplexName)->get();

                    if ($check->count() > 0) {
                        echo "----" . $num - 1 . "--------- <b>" . $check[0]['strKey'] . " & " . $unit . " <- updated new key   </b>  user already has a unit in this complex - add it " . "<br>";
                        $this->AddRemoveUnit($properties[$p], $updatesA, $unit, $check);
                        ///                     // $this->ADDunit($updatesA, $unit, $database, $check[0], $properties[$p]);

                    } else {
                        echo "----" . $num - 1 . "--------- <b>" . $strComplexName . " " . $unit . " <- add new key   </b> " . "<br>";
                        // send old prop
                        // new prop
                        // new unit
                        $this->AddRemoveUnit($properties[$p], $updatesA, $unit, $check);
                    }
                } else {
                    echo " no update ------" . "<br>";
                }
            } else {

            }

        }

        if ($num == 1) {

            echo "-------" . $num . "<b>no match for this unit</b><br>";

            // check if the owner already has a unit in this complex
            $check = Property::on($database)->where('strIdentity', $sellerId)->where('strComplexName', $strComplexName)->get();

            if ($check->count() > 0) {
                echo "----" . $num . "--------- <b>" . $check[0]['strKey'] . " & " . $unit . " <- updated new key   </b>  user already has a unit in this complex - add it " . "<br>";

//////   working on this
                $this->ADDunit($updatesA, $unit, $database, $check[0], 'nomatch');

            } else {

                //  this unit is not in properties
                //  the owner owns no units in the complex
                //  ADD

                echo "----" . $num . "owner has no units in this complex " . " <- add new key   </b> " . "<br>";

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

    public function sortUnits($strComplexNo)
    {

        $a = explode(" & ", $strComplexNo);

        sort($a);

        $a = implode(" & ", $a);

        return $a;
    }

    public function newUnit($updatesA, $unit, $database)
    {

        $now            = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
        $updatesA['id'] = null;

        $updatesA['strKey']      = $updatesA['strComplexName'] . " " . $unit;
        $updatesA['numStreetNo'] = $updatesA['strStreetNo'];

        $updatesA['numComplexNo'] = $unit;
        $updatesA['strComplexNo'] = $unit;
        $updatesA['created_at']   = $now;

        // delete older ones
        $delOld = Property::on($database)->where('strKey', $updatesA['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA['dtmRegDate']))->delete();
        echo "..DELETE OLD ............    " . $updatesA['strKey'] . "   -   " . $delOld . "<br>";

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

        echo "newUnit completed" . "<br><br>";

    }

    public function ADDUnit($updatesA, $unit, $database, $ownerRecord, $oldkey)
    {

        echo " add unit to owner record " . $ownerRecord['id'] . "<br>";

        if ($oldkey != 'nomatch') {
            echo " remove unit from old key " . $oldkey['strKey'] . "<br>";
        } else {
            echo " no old key just add - " . $oldkey . "<br>";
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
            echo "     **  DualOwner " . "<br>";
        }

        //unit is in key dont add unless it is a dual owner
        if ($test && !$dual) {

            echo "     **   unit already in key " . $ownerRecord['strComplexNo'] . " - unit " . $unit . " - " . $test . "<br>";
            echo "     **   Not dualOwner " . $ownerRecord['strIdentity'] . " - " . $updatesA['strIdentity'] . " - " . $ownerRecord['strTitleDeed'] . " - " . $updatesA['strTitleDeed'] . "<br>";
            // add key
        } else {

            //dd($updatesA, $unit, $database, $ownerRecord);

            $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

            unset($updatesA['id']);

            $updatesA['strKey']       = $ownerRecord['strKey'] . ' & ' . $unit;
            $updatesA['numStreetNo']  = $updatesA['strStreetNo'];
            $updatesA['strComplexNo'] = $ownerRecord['strComplexNo'] . ' & ' . $unit;
            $updatesA['numComplexNo'] = $updatesA['strComplexNo'];
            $updatesA['strSqMeters']  = $ownerRecord['strSqMeters'] . ' & ' . $updatesA['strSqMeters'];
            $updatesA['created_at']   = $now;
            $updatesA['updated_at']   = $now;

            echo "     Sorted key ----" . $this->sortUnits($updatesA['strComplexNo']) . "<br>";

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

            echo "ADDUnit completed" . "<br><br>";
        }
    }

//add remove unit
    public function AddRemoveUnit($oldprop, $newprop, $unit, $pastowner)
    {

        //   $sortunits = $this->sortUnits("10 & 40 & 50 & 1");
        //   echo $sortunits;
        //   dd($oldprop['strKey'], $newprop['strKey'], $unit);
        echo "ADD REMOVE UNIT   >>>>>" . "<br>";
        echo "old " . $oldprop['strKey'] . "<br>";
        echo "unit " . $unit . "<br>";
        echo "updated " . $this->removeUnit($unit, $oldprop['strComplexNo'], $oldprop['strComplexName']) . "<br>";
        echo "new " . $newprop['strKey'] . "<br>";
        echo "count  " . $pastowner->count() . "<br>";
        echo "already has unit  " . $pastowner[0]['strKey'] . "<br>";
    }

} // controller end
