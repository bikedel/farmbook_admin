<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\CsvFileImporter;
use Redirect;
use Session;
use Exception;
use DB;
use Storage;
use App\Farmbook;

class CsvImportController extends BaseController
{



	public function __construct()
	{
		$this->middleware('auth');


	}


    /**
     * [POST] Form which will submit the file
     */
    public function index()
    {
      $database = 'information_schema';
      $otf = new \App\Database\OTF(['database' =>  $database]);
      $db = DB::connection( $database);

      $data = $db->table('schemata')->select('schema_name')->where('schema_name','like','%farm%')->lists("schema_name","schema_name");



     return view('import',compact('data'));      //,compact('streets','street'));

}







    /**
     * [POST] Form which will submit the file
     */
    public function store(Request $request)
    {

       $database = $request->input('database');


//  dd('CsvImportController  hi hi',$request, $database);
        // Check if form submitted a file
       if ($request->hasFile('csv_import')) {
          $csv_file = $request->file('csv_import');

            // You wish to do file validation at this point
          if ($csv_file->isValid()) {

                // We can also create a CsvStructureValidator class
                // So that we can validate the structure of our CSV file

                // Lets construct our importer
             $csv_importer = new CsvFileImporter();

                // Import our csv file
             if ($csv_importer->import($csv_file,$database) ){
                    // add to farmbooks
                $Farmbook = new Farmbook;
                $Farmbook->name = $database;
                $Farmbook->database = $database;
                $Farmbook->type = 0;
                $Farmbook->save();
                    // Provide success message to the user
                $message = 'Your file has been successfully imported! ';
                Session::flash('flash_message', 'Your file has been successfully imported! ' );
                Session::flash('flash_type', 'alert-success');


            } else {
                $message = 'Your file did not import ';
                Session::flash('flash_message', 'Your file did not import ');
                Session::flash('flash_type', 'alert-danger');
            }

        } else {
                // Provide a meaningful error message to the user
                // Perform any logging if necessary
         $message = 'You must provide a CSV file for import.';
         Session::flash('flash_message', 'You must provide a CSV file for import.' );
         Session::flash('flash_type', 'alert-danger');
     }

     return Redirect::back()->with('flash_message',$message);

 } else {
    $message = 'You must provide a CSV file for import.';
    Session::flash('flash_message', 'You must provide a CSV file for import.' );
    Session::flash('flash_type', 'alert-danger');
}

return Redirect::back()->with('flash_message',$message);
}


    // create database
public function createdatabase(Request $request)
{

    $file = storage_path().'/databases/dummy_database.sql' ;

    $file2 = storage_path().'/databases/dummy_database.sql' ;

      // check if the file exists
    if (file_exists($file)) {
      $sqldump =  file_get_contents($file );
  } else {
      $file = "not found";
      $message = 'missing dummy_database';
      Session::flash('flash_message',    $message);
      Session::flash('flash_type', 'alert-danger');
      return Redirect::back()->with('flash_message',$message);
  }



  $database = $request->input('database');
  $database =  $database . '_farmbook';


  $servername = config('database.connections.mysql.host');
  $username = config('database.connections.mysql.username');
  $password = config('database.connections.mysql.password');
       // dd('make database',$database);

  $dbname = 'tmp';

         // connect to tmp database
  $otf = new \App\Database\OTF(['database' => $dbname]);
  $db = DB::connection($dbname);

  $sql = "CREATE DATABASE ".$database;



    // database created success
  if ($db->getpdo()->exec(  $sql)) {

        // connect to the new database
    $otf = new \App\Database\OTF(['database' => $database]);
    $db = DB::connection($database);  

        // creates tables with dummy
    $db->getpdo()->exec( $sqldump);

    $message = $database. ' created successfully.';
    Session::flash('flash_message',    $message);
    Session::flash('flash_type', 'alert-success');

} else {
    $message = $database. ' '. mysqli_error($conn);
    Session::flash('flash_message',    $message);
    Session::flash('flash_type', 'alert-danger');
}

mysqli_close($conn);
return Redirect::back()->with('flash_message',$message);

}
}