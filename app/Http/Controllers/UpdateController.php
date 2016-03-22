<?php

namespace App\Http\Controllers;

use App\Note;
use App\Owner;
use App\Property;
use App\Update;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

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

    public function update()
    {

        $del        = 0;
        $add        = 0;
        $tot        = 0;
        $lastupdate = "none";
        $now        = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // dummy database
        $database = 'testing_updates';
        $otf      = new \App\Database\OTF(['database' => $database]);
        $db       = DB::connection($database);

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

                echo $updatesA[$x]['strKey'] . '   -   ' . $updatesA[$x]['dtmRegDate'] . "  -  New Owner - " . $updatesA[$x]['strOwners'] . "  -  Seller - " . $updatesA[$x]['strSellers'] . "<br>";

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
                    echo "----- " . "Already in database    " . $updatesA[$x]['strKey'] . '<br>';
                }

                // get the properties we will delete for the report
                $delp = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->get();

                // delete them
                Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->delete();

                // report
                $properties = $delp->toArray();
                for ($i = 0; $i <= sizeof($properties) - 1; $i++) {
                    $del++;
                    echo "---" . $properties[$i]['strKey'] . '   -   ' . $properties[$i]['dtmRegDate'] . "  - Old owner - " . $properties[$i]['strOwners'] . "<br>";
                }

            }

        }

        echo 'Deleted : ' . $del . "<br>";
        echo 'Added : ' . $add . "<br>";
        echo 'Total : ' . ($add - $del) . "<br>";

    }

    public function update2()
    {

        $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();

        // dummy database
        $database = 'testing_updates';
        $otf      = new \App\Database\OTF(['database' => $database]);
        $db       = DB::connection($database);

        // get all records
        $updates    = Update::on($database)->orderBy('strKey')->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->get();
        $properties = Property::on($database)->orderBy('strKey')->orderBy(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"))->get();

        // convert to arrays
        $updatesA    = $updates->toArray();
        $propertiesA = $properties->toArray();

        $lastupdate = "none";

        // loop through updates
        for ($x = 0; $x <= sizeof($updatesA) - 1; $x++) {

            // Search for strKey in the properties table
            $found = array_search($updatesA[$x]['strKey'], array_column($propertiesA, 'strKey'));

            if ($found > 0) {

                //  the record exists in Properties
                echo $x . ' Found  ' . $updatesA[$x]['strKey'] . '   -   ' . $updatesA[$x]['dtmRegDate'] . "<br>";

                //  $propDate   = new Carbon($propertiesA[$found]['dtmRegDate'], 'Africa/Johannesburg');
                //  $updateDate = new Carbon($updatesA[$x]['dtmRegDate'], 'Africa/Johannesburg');

                // search prooperty for all keys and delete those will older reg dates
                // -delete appears to not be working
                //  $tit = Property::on($database)->select(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d') as date_format"))->where('date_format', '<', "2019-01-01")->get();
                $tit = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->delete();

                //dd($tit, $updatesA[$x]['strKey'], $updatesA[$x]['dtmRegDate'], $updates);

                //      where('date_format', '<', DB::raw("STR_TO_DATE($updatesA[$x][dtmRegDate], '%Y/%m/%d')"));
                //          DB::raw('str_to_date($updatesA[$x][dtmRegDate], '%Y,%m,%d')')->delete();

                // set id to null so autoincrement works and no violation
                $updatesA[$x]['id']           = null;
                $updatesA[$x]['numStreetNo']  = $updatesA[$x]['strStreetNo'];
                $updatesA[$x]['numComplexNo'] = $updatesA[$x]['strComplexNo'];

                // insert updated property only if it is newer
                $new = Property::on($database)->where('strKey', '=', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<=', Date($updatesA[$x]['dtmRegDate']))->get();
                if ($new->count() == 0) {

                    //add updated at to end of the array
                    $updatesA[$x]['updated_at'] = $now;

                    Property::on($database)->insert($updatesA[$x]);

                    // add owner record if not existing
                    $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA[$x]['strIdentity']);
                    if ($hascontact->count() == 0) {
                        $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA[$x]['strIdentity'], 'NAME' => $updatesA[$x]['strOwners']));
                    } else {
                        // dont wipe old details

                    }

                    // add note
                    $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA[$x]['strKey']);
                    if ($hasnote->count() == 0) {
                        $note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => $now . ' Notes started.', 'updated_at' => $now));
                        $lastupdate = $updatesA[$x]['strKey'];
                    } else {
                        if ($lastupdate != $updatesA[$x]['strKey']) {
                            $note       = Note::on($database)->where('strKey', '=', $updatesA[$x]['strKey'])->update(array('memNotes' => DB::raw('concat(memNotes, " \n' . $now . ' New Owner.")'), 'updated_at' => $now));
                            $lastupdate = $updatesA[$x]['strKey'];
                        }
                    }
                }
            } else {

                //  KEY not found so it is a new record ADD
                // echo 'New  ' . $updatesA[$x]['strKey'] . '  new record  owner[' . $updatesA[$x]['strOwners'] . ']' . "<br>";
                echo $x . ' New   ' . $updatesA[$x]['strKey'] . '    -    ' . $updatesA[$x]['dtmRegDate'] . "<br>";

                // set id to null so autoincrement works and no violation
                $updatesA[$x]['id']           = null;
                $updatesA[$x]['numStreetNo']  = $updatesA[$x]['strStreetNo'];
                $updatesA[$x]['numComplexNo'] = $updatesA[$x]['strComplexNo'];

                // add property
                //  $newprop = Property::on($database)->insert(array('numErf' => "u" . $updatesA[$x]['numErf']));
                //add updated at to end of the array
                $updatesA[$x]['updated_at'] = $now;
                $newprop                    = Property::on($database)->insert($updatesA[$x]);

                //delete older ones
                //  $tilt = Property::on($database)->where('strKey', $updatesA[$x]['strKey'])->where(DB::raw("STR_TO_DATE(dtmRegDate, '%Y-%m-%d')"), '<', Date($updatesA[$x]['dtmRegDate']))->where('updated_at', $now)->delete();

                // add owner record if not existing
                $hascontact = Owner::on($database)->select('id')->where('strIDNumber', '=', $updatesA[$x]['strIdentity']);
                if ($hascontact->count() == 0) {
                    $owner = Owner::on($database)->insert(array('strIDNumber' => $updatesA[$x]['strIdentity'], 'NAME' => $updatesA[$x]['strOwners']));
                }

                // add note
                $hasnote = Note::on($database)->select('id')->where('strKey', '=', $updatesA[$x]['strKey']);
                if ($hasnote->count() == 0) {
                    $note       = Note::on($database)->insert(array('strKey' => $updatesA[$x]['strKey'], 'numErf' => $updatesA[$x]['numErf'], 'memNotes' => $now . ' Notes started.', 'updated_at' => $now));
                    $lastupdate = $updatesA[$x]['strKey'];
                } else {
                    if ($lastupdate != $updatesA[$x]['strKey']) {
                        $note       = Note::on($database)->where('strKey', '=', $updatesA[$x]['strKey'])->update(array('memNotes' => DB::raw('concat(memNotes, " \n' . $now . ' New Owner.")'), 'updated_at' => $now));
                        $lastupdate = $updatesA[$x]['strKey'];
                    }
                }

            }

        }

        dd('end');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
