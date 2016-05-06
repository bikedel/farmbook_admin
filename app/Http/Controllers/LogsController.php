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
            File::put($filename, 'date,user,action,comment' . "\r\n");
            File::append($filename, \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ",ADMIN" . ", Log started.");
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
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function listlogs()
    {

        $directory             = storage_path('updates/tmp');
        $destination_directory = public_path();
        $files                 = File::allFiles($directory);
        //dd($directory, $files, "you got here - list update logs");

        $logs  = array();
        $times = array();
        $links = array();

        foreach ($files as $file) {
            if ($file->getExtension() == "log") {

                //echo (string) $file->getPathname(), "<br>";
                $filename = $file->getFilename();
                $path     = $file->getrealPath();
                //$path = $file->getPathname();

                $time = date("F j, Y, g:i a", $file->getCtime());

                //filename
                array_push($logs, $filename);
                //time and date of update
                array_push($times, $time);
                // link
                // array_push($links, $filename);

                //   $exists  = Storage::exists($directory . $filename);
                //  $exists2 = Storage::exists('/storage/updates/tmp/' . $filename);

                //     dd($exists, $exists2, $file, $path);
                //    Storage::copy($directory . $filename, url($filename));

                $file_path = storage_path() . '/updates/tmp/' . $filename;

                $destination = $destination_directory . '/' . $filename;

                $t = "http://localhost/laravel/farmbook_admin/storgae/updates/tmp/" . $filename;

                if (file_exists($file_path)) {
                    File::copy($file_path, $destination);
                    //   $entry->mime = $file->getClientMimeType();

                } else {
                    // dd("not found");

                }

                array_push($links, $filename);

            }
        }

        return view('updatelogs', compact('logs', 'times', 'links'));
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
