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
use App\Owner;

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

      $data = $db->table('schemata')->select('schema_name')->where('schema_name','like','%farmbook2%')->orderBy('schema_name')->lists("schema_name","schema_name");

      $type = ['FH','ST'];




      $type = array_combine($type, $type);



     return view('import',compact('data','type'));      //,compact('streets','street'));

   }







    /**
     * [POST] Form which will submit the file
     */
    public function store(Request $request)
    {
        // check a file has been selected
     if (!$request->hasFile('csv_import')) {
      $message = 'Please select a CSV file to import.';
      Session::flash('flash_message', 'You must provide a CSV file for import.' );
      Session::flash('flash_type', 'alert-danger');
      return Redirect::back()->with('flash_message',$message);
    }

    if ($request->hasFile('csv_import')) {


      $csv_file = $request->file('csv_import');


      $filename = $csv_file->getClientOriginalName();
      $fileinfo = pathinfo($filename);

      // check the file has a .csv extention
      if (strtoupper($fileinfo['extension']) !== 'CSV' ){
        $message = 'You must provide a CSV file for import.';
        Session::flash('flash_message', 'You must provide a CSV file for import.' );
        Session::flash('flash_type', 'alert-danger');
        return Redirect::back()->with('flash_message',$message);
      }



      $valid = 0;

    if (strpos($fileinfo['filename'], 'FH') !== false) {
       $type="FH";
       $valid = 1;
    }
     if (strpos($fileinfo['filename'], 'ST') !== false) {
       $type="ST";
       $valid = 1;
     }

    // check the filename starts with ST or FH - ie made fom paul's saptg program
     if ($valid !== 1 ){
      $message = 'Please provide a valid SAPTG file';
      Session::flash('flash_message', 'You must provide a CSV file for import.' );
      Session::flash('flash_type', 'alert-danger');
      return Redirect::back()->with('flash_message',$message);
    }



   // make the databse name
    $startpos = strrpos($fileinfo['filename'], '_')+1;
    $endpos = strrpos($fileinfo['filename'], ' ');

    $len = $endpos - $startpos;
    $name = substr($fileinfo['filename'],$startpos,$len);

    $normal_name = $name . ' '.$type;

    $name = str_replace(' ','_',$name).'_'.$type.'_'.'farmbook2';

    $database =  $name;



 // check if the database exists - if not create it and import
    $schema = 'information_schema';
    $otf = new \App\Database\OTF(['database' =>  $schema]);
    $db = DB::connection( $schema);

    $data = $db->table('schemata')->select('schema_name')->where('schema_name','like','%farmb%')->orderBy('schema_name')->lists("schema_name","schema_name");


    
    $found =  array_search($database,$data);

 //   dd($data,$database,$found,"egfdsfdf");



$dbname = 'tmp';

         // connect to tmp database
  $otf = new \App\Database\OTF(['database' => $dbname]);
  $db = DB::connection($dbname);

  $sql = "CREATE DATABASE ".$database;


  //set created to false
  $created = false;

  try {
    // created database successfully
    $db->getpdo()->exec(  $sql);
    $created = true ;

  } catch (Exception $ex) {

    // dd( $ex->getMessage());
    // error creating database
    $message =  $ex->getMessage();



  }

  $file = storage_path().'/databases/dummy_database.sql' ;


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

    // database created success
  if ($created == true) {

        // connect to the new database
    $otf = new \App\Database\OTF(['database' => $database]);
    $db = DB::connection($database);  

        // creates tables with dummy
    $db->getpdo()->exec( $sqldump);

}






            // You wish to do file validation at this point
    if ($csv_file->isValid()) {

                // We can also create a CsvStructureValidator class
                // So that we can validate the structure of our CSV file

                // Lets construct our importer
     $csv_importer = new CsvFileImporter();

                // Import our csv file
     if ($csv_importer->import($csv_file,$database) ){

      $Farmbook = Farmbook::where('database','=',$database);





















      if ($Farmbook->count() > 0 )
      {

        $Farmbook = Farmbook::where('database','=',$database)->update(['database'=> $database]);

      } else {
                // add to farmbooks
        $Farmbook = new Farmbook;
        $Farmbook->name = $normal_name ;
        $Farmbook->database = $database;
        $Farmbook->type = 0;
        $Farmbook->save();
      }



                // Provide success message to the user
      $message = $filename.' has been successfully imported to '.$database;
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
public function deletedatabase(Request $request)

{
 $database = $request->input('database');


 $servername = config('database.connections.mysql.host');
 $username = config('database.connections.mysql.username');
 $password = config('database.connections.mysql.password');
       // dd('make database',$database);

 $dbname = 'tmp';

         // connect to tmp database
 $otf = new \App\Database\OTF(['database' => $dbname]);
 $db = DB::connection($dbname);

 $sql = "DROP DATABASE ".$database;


  //set created to false
 $created = false;

 try {
    // delete database 
  $db->getpdo()->exec(  $sql);
  $created = true ;
  $Farmbook = Farmbook::where('database','=',$database)->delete();

} catch (Exception $ex) {

    // dd( $ex->getMessage());
    // error creating database
  $message =  $ex->getMessage();
}

    // database created success
if ($created == true) {

  $message = $database. ' deleted successfully.';
  Session::flash('flash_message',    $message);
  Session::flash('flash_type', 'alert-success');

} else {

  Session::flash('flash_message',    $message);
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
  $type = $request->input('type');
  $database =   $database . '_'.$type.'_farmbook2';
  //dd($database);

  $servername = config('database.connections.mysql.host');
  $username = config('database.connections.mysql.username');
  $password = config('database.connections.mysql.password');
       // dd('make database',$database);

  $dbname = 'tmp';

         // connect to tmp database
  $otf = new \App\Database\OTF(['database' => $dbname]);
  $db = DB::connection($dbname);

  $sql = "CREATE DATABASE ".$database;


  //set created to false
  $created = false;

  try {
    // created database successfully
    $db->getpdo()->exec(  $sql);
    $created = true ;

  } catch (Exception $ex) {

    // dd( $ex->getMessage());
    // error creating database
    $message =  $ex->getMessage();



  }



    // database created success
  if ($created == true) {

        // connect to the new database
    $otf = new \App\Database\OTF(['database' => $database]);
    $db = DB::connection($database);  

        // creates tables with dummy
    $db->getpdo()->exec( $sqldump);

    $message = $database. ' created successfully.';
    Session::flash('flash_message',    $message);
    Session::flash('flash_type', 'alert-success');

  } else {

    Session::flash('flash_message',    $message);
    Session::flash('flash_type', 'alert-danger');
  }

  return Redirect::back()->with('flash_message',$message);

}
}