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
        $updates = Update::on($database)->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->orderBy('strTitleDeed')->get();

        // convert to array
        //
        $updatesA = $updates->toArray();

        echo 'updates to process = <b>' . $updates->count() . "</b><br>";

        // process update records
        //
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // fetch all property records for the complex where the reg date is older than the update
            //  $properties = Property::on($database)->orderBy('strKey')->where('strComplexName', $updatesA[$x]['strComplexName'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

            // check if there is an exact match on strKey
            //  $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

            $units     = $this->noOfUnits($updatesA[$x]['strComplexNo']);
            $arr_units = $this->arrOfUnits($updatesA[$x]['strComplexNo']);
            echo '...........................................................................................................................................' . "<br>";
            echo $updatesA[$x]['strKey'] . "<br>";
            echo $units . ' units  -> ' . implode(" ", $arr_units) . "<br>";

            // check if there is an exact match on strKey with an earlier date
            $exact = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->get();

            if ($exact->count() > 0) {

                echo ' - <b>' . $exact->count() . '</b>  key is in properties' . "<br>";
                Update::on($database)->where('strKey', $updatesA[$x]['strKey'])->update(['deleted' => 1]);

            } else {
                echo ' - <b>' . $exact->count() . '</b>  key is NOT in properties' . "<br>";
            }

/*
echo ($x + 1) . "---------------------------------------------------------------------------------------" . "<br>";
echo 'regDate = <b>' . $updatesA[$x]['dtmRegDate'] . "</b><br>";
echo 'strKey  = <b>' . $updatesA[$x]['strKey'] . "</b><br>";
echo 'Owner   = ' . $updatesA[$x]['strOwners'] . "  |  " . 'Seller  = ' . $updatesA[$x]['strSellers'] . "<br>";
echo "<br>";
echo ' - ' . $units . ' unit(s) in ' . $updatesA[$x]['strComplexName'] . "<br>";
echo ' - <b>' . $exact->count() . '</b>  exact match(es)' . "<br>";
echo "<br>";
for ($u = 0; $u < $units; $u++) {

echo " -- Unit" . ($u + 1) . " - <b>" . $arr_units[$u] . "</b> <br>";

echo $this->findUnit($updatesA[$x]['strComplexName'], $arr_units[$u], $properties, $updatesA[$x]['dtmRegDate'], $updatesA[$x]['strSellers'], $updatesA[$x]['strIdentity']);

}
 */
            echo "<br>";
        }

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
    public function findUnit($strComplexName, $unit, $properties, $updateDate, $sellers, $sellerId)
    {

        $unit = ltrim(rtrim($unit));
        $num  = 1;
        for ($p = 0; $p < $properties->count(); $p++) {

            // echo $properties[$p]['strKey'] . "<br>";

            // if there is no & - then it is a single property and explode by " "

            $punits = $this->arrOfUnits($properties[$p]['strComplexNo']);

            //  dd(strpos($properties[$p]['strComplexNo'], "&"), $properties[$p]['strComplexNo']);

            $found = in_array($unit, $punits, true);

            // dd($unit, $punits, $found);

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
                    $checkdate = "&#10006" . "&#10060" . "Reg Date is the same";
                } else {
                    $checkdate = "&#10006" . "&#10060" . "   Reg Date is more recent than the update";
                }

                echo "----" . $num . "--------- " . $properties[$p]['strOwners'] . "    |    " . $properties[$p]['dtmRegDate'] . " - <b> " . $checkdate . "</b><br>";
                echo "----" . $num . "--------- " . $properties[$p]['strKey'] . "<br>";

                // remove unit from key
                $newKey = $this->removeUnit($unit, $properties[$p]['strComplexNo'], $properties[$p]['strComplexName']);
                if ($newKey == " DELETE ") {
                    echo "----" . $num . "--------- <b>" . "delete and add" . "</b><br>";
                } else {
                    echo "----" . $num . "--------- <b>" . $this->removeUnit($unit, $properties[$p]['strComplexNo'], $properties[$p]['strComplexName']) . " <- new key</b><br>";
                }
                // check if owner is seller
                if ($properties[$p]['strIdentity'] == $sellerId) {
                    echo "----" . $num . "--------- <b>" . "Seller = Buyer" . "</b><br>";
                }

                // units in key
                echo "------- seller owns " . sizeof($punits) . "  unit(s)" . "<br><br>";
                // $this->removeUnit($unit, $properties[$p]['strComplexNo']);

            } else {
            }
            if ($num == 1) {

                //echo "-------" . $num . "<b>no existing record</b><br>";
            }

        }

    }

    /**
     *  remove unit from key
     *
     * @return string new key
     */
    public function removeUnit($unit, $strKey, $complex)
    {

        $a = explode(" & ", $strKey);

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

}
