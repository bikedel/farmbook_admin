<?php

namespace App\Http\Controllers;

use App\Farmbook;
use App\Suburb;
use Carbon;
use DB;
use Illuminate\Http\Request;
use Redirect;
use Session;

class FarmbookController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        // dd("user controller");

        $farmbooks = Farmbook::orderBy('name')->get();

        return view('farmbooks', compact('farmbooks'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        // dd("user controller EDIT ",$id);

        $farmbooks = Farmbook::where('id', '=', $id)->get();
        $suburbs   = Suburb::lists('name', 'id');

        // fetch suburbs associated with farmbook
        $suburb_farmbooks = $farmbooks->first()->suburbs()->get();

        $suburb_farmbooks = array_pluck($suburb_farmbooks, 'id');

        // dd($suburb_farmbooks);
        //dd($users ,$user_farmbooks,$farmbooks );
        return view('editfarmbook', compact('farmbooks', 'suburbs', 'suburb_farmbooks'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {

        // dd("user controller EDIT ",$id);

        $farmbooks = Farmbook::find($id);

        //  dd($farmbooks->database,$id);
        $database = $farmbooks->database;

        $farmbooks->delete();

        $dbname = 'tmp';

        // connect to tmp database
        $otf = new \App\Database\OTF(['database' => $dbname]);
        $db  = DB::connection($dbname);

        $sql = "DROP DATABASE " . $database;

        //set created to false
        $created = false;

        try {
            // delete database
            $db->getpdo()->exec($sql);
            $created = true;

        } catch (Exception $ex) {

            // dd( $ex->getMessage());
            // error creating database
            $message = $ex->getMessage();
            Session::flash('flash_message', 'Error deleting ' . $message);
            Session::flash('flash_type', 'alert-warning');
            return Redirect::back();
        }

        $now = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();

        Session::flash('flash_message', 'Farmbook deleted ' . ' at ' . $now);
        Session::flash('flash_type', 'alert-success');
        return Redirect::back();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {

        // current timestamp
        $now = Carbon\Carbon::now('Africa/Cairo')->toDateTimeString();

        // get inpute
        $id       = $request->input('id');
        $name     = $request->input('name');
        $database = $request->input('database');
        $suburbs  = $request->input('suburbs');

        //dd($suburbs);
        //$type     = $request->input('type');

        Farmbook::where('id', $id)->update(array('name' => $name, 'database' => $database, 'updated_at' => $now));

        $farmbook = Farmbook::where('id', '=', $id)->first();
        // store the deed suburbs

        $farmbook->suburbs()->sync($suburbs);

        //  dd("user controller Store ",$id,,$farmbooks);
        Session::flash('flash_message', 'Updated ' . $name . ' at ' . $now);
        Session::flash('flash_type', 'alert-success');
        return Redirect::back();
    }

}
