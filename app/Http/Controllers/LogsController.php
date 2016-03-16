<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use File;
use Illuminate\Http\Request;
use Redirect;
use Response;
use Storage;

class LogsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try
        {
            $filename = storage_path() . '/app/' . 'logfile.txt';
            $contents = File::get($filename);
        } catch (\Exception $e) {

            $filename = storage_path() . '/app/' . 'logfile.txt';
            File::put($filename, \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . " Log started.");
            $contents = File::get($filename);
        }

        $logs = explode(PHP_EOL, $contents);

/*  CHARTS

$u        = User::count();
$f        = Farmbook::count();
$c        = Contact::count();
$filename = storage_path() . '/app/' . 'logfile.txt';

$junkTable = Lava::DataTable(); // Lava::DataTable() if using Laravel

$junkTable->addStringColumn('Type')
->addNumberColumn('Value')
->addRow(['Users', $u])
->addRow(['Farmbooks', $f])
->addRow(['Logs', sizeof($logs)])
->addRow(['Contacts', $c])
;

$chart3 = Lava::GaugeChart('Temps', $junkTable, [
'width'      => 600,
'greenFrom'  => 0,
'greenTo'    => 69,
'yellowFrom' => 70,
'yellowTo'   => 89,
'redFrom'    => 90,
'redTo'      => 100,
'majorTicks' => [
'Safe',
'Critical',
],
]);
//$logs = array_reverse($logs ) ;

//dd($contents,$csv);

 */

        return view('logs', compact('logs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {

        $now = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString();
        $now = str_replace(':', '-', $now);
        $now = str_replace(' ', '-', $now);

        $filename = storage_path() . '/app/' . 'logfile.txt';
        $newfile  = storage_path() . '/app/' . 'logfile_' . $now . '.txt';
        File::move($filename, $newfile);

        // file::delete($filename);
        return Redirect::back();
    }
}
