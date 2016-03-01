<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Exception;
use File;

class CsvFileImporter
{
    /**
     * Import method used for saving file and importing it using a database query
     * 
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $csv_import
     * @return int number of lines imported
     */
    public function import($csv_import,$database)
    {
        // Save file to temp directory
        $moved_file = $this->moveFile($csv_import);

        // Normalize line endings
        $normalized_file = $this->normalize($moved_file);

        // Import contents of the file into database
        $result = $this->importFileContents($normalized_file,$database);


        return true;
    }

    /**
     * Move File to a temporary storage directory for processing
     * temporary directory must have 0755 permissions in order to be processed
     *
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $csv_import
     * @return Symfony\Component\HttpFoundation\File $moved_file
     */
    private function moveFile($csv_import)
    {
        // Check if directory exists make sure it has correct permissions, if not make it
        $destination_directory = storage_path('imports/tmp');
        if (is_dir( $destination_directory) )
        {
            //chmod($destination_directory, 0755);
        } else {

            //mkdir($destination_directory, 0755, true);
        }

        // Get file's original name
        $original_file_name = $csv_import->getClientOriginalName();

 if (is_file( $destination_directory.'/'.$original_file_name) ){

    File::delete( $destination_directory.'/'.$original_file_name);
 }


        // Return moved file as File object
        return $csv_import->move($destination_directory, $original_file_name);
    }

    /**
     * Convert file line endings to uniform "\r\n" to solve for EOL issues
     * Files that are created on different platforms use different EOL characters
     * This method will convert all line endings to Unix uniform
     *
     * @param string $file_path
     * @return string $file_path
     */
    protected function normalize($file_path)
    {
        //Load the file into a string
        $string = @file_get_contents($file_path);

        if (!$string) {
            return $file_path;
        }

        //Convert all line-endings using regular expression
        $string = preg_replace('~\r\n?~', "\n", $string);

        file_put_contents($file_path, $string);

        return $file_path;
    }

    /**
     * Import CSV file into Database using LOAD DATA LOCAL INFILE function
     *
     * NOTE: PDO settings must have attribute PDO::MYSQL_ATTR_LOCAL_INFILE => true
     *
     * @param $file_path
     * @return mixed Will return number of lines imported by the query
     */
    private function importFileContents($file_path,$database)
    {


        $chost = config('database.connections.mysql.host');
        $cuser = config('database.connections.mysql.username');
        $cpass = config('database.connections.mysql.password');
        $user = $cuser;
        $password = $cpass;
        $host = $chost;
        $dbname  = $database;
        $dsn = 'mysql:dbname=test1;';

         // connect to tmp database
        $otf = new \App\Database\OTF(['database' => $dbname]);
        $db = DB::connection($dbname);


        // delete records and import
        $query_delete = ('TRUNCATE TABLE complexes');
        $query_delete1 = ('TRUNCATE TABLE notes');
        $query_delete2 = ('TRUNCATE TABLE owners');
        $query_delete3 = ('TRUNCATE TABLE properties');
        $query_delete4 = ('TRUNCATE TABLE streets');

  $key= "CONCAT(numErf,'-', numPortion)";

if (strpos($database, '_FH') !== false) {
   $key= "CONCAT(numErf,'-', numPortion)";
}
if (strpos($database, '_ST') !== false) {
    $key= "CONCAT(strComplexName,' ', strComplexNo)";
}

//dd($key);

        $query = sprintf("LOAD DATA INFILE '%s' REPLACE INTO TABLE properties 
            FIELDS TERMINATED BY ','  ENCLOSED BY '\"' LINES TERMINATED BY '\n'  IGNORE 1 LINES 
            (
             strSuburb,
             numErf,
             numPortion,
             strStreetNo,
             strStreetName,
             strSqMeters,
             strComplexNo,
             strComplexName,
             dtmRegDate,
             strAmount,
             strBondHolder,
             strBondAmount,
             strOwners,
             strIdentity,
             strSellers,
             strTitleDeed  )
        SET strKey = ".$key , addslashes($file_path) );


        $query_makeStreets = ('INSERT INTO streets (strStreetName) SELECT strStreetName FROM properties GROUP BY strStreetName');

        $query_makeComplex =  ('INSERT INTO complexes (strComplexName) SELECT strComplexName FROM properties GROUP BY strComplexName');

        //$query_makeErfs = ('INSERT INTO notes (numErf) SELECT numErf FROM tblSuburbOwners GROUP BY numErf');
        $query_comlexNo = ('UPDATE properties SET numComplexNo = strComplexNo');

        $query_streetNo = ('UPDATE properties SET numStreetNo = strStreetNo');

        $query_makeMems = ('INSERT INTO notes (numErf,strKey) SELECT numErf,strKey FROM properties ');


        $query_makeContacts = ('INSERT INTO owners (strIDNumber,NAME) SELECT strIdentity,strOwners FROM properties group by strIdentity');

        
        $query_updateContacts = ('UPDATE owners, farmbook_admin.contacts
            SET owners.NAME = farmbook_admin.contacts.NAME,
            owners.strSurname = farmbook_admin.contacts.strSurname,
            owners.strFirstName = farmbook_admin.contacts.strFirstName,
            owners.strHomePhoneNo = farmbook_admin.contacts.strHomePhoneNo,
            owners.strWorkPhoneNo = farmbook_admin.contacts.strWorkPhoneNo,
            owners.strCellPhoneNo = farmbook_admin.contacts.strCellPhoneNo,
            owners.EMAIL = farmbook_admin.contacts.EMAIL
            WHERE owners.strIDNumber = farmbook_admin.contacts.strIDNumber');

        $query_insertContacts =  ('INSERT INTO farmbook_admin.contactsnew (strIDNumber,TITLE,INITIALS,NAME,strSurname,strFirstName,strHomePhoneNo,strWorkPhoneNo,strCellPhoneNo,EMAIL) 
            SELECT strIDNumber,TITLE,INITIALS,NAME,strSurname,strFirstName,strHomePhoneNo,strWorkPhoneNo,strCellPhoneNo,EMAIL FROM Owners');

        try {
            //delete
            $db->getpdo()->exec( $query_delete);
            $db->getpdo()->exec( $query_delete1);
            $db->getpdo()->exec( $query_delete2);
            $db->getpdo()->exec( $query_delete3);
            $db->getpdo()->exec( $query_delete4);
           // $db->getpdo()->exec( $query_delete5);

            //import owners
            $result =  $db->getpdo()->exec($query);
            // create street
            $db->getpdo()->exec( $query_makeStreets);
            // make complex
            $db->getpdo()->exec( $query_makeComplex);
            // make Erf
           // $db->getpdo()->exec( $query_makeErfs);
            // make Mem
            $db->getpdo()->exec( $query_makeMems);

            $db->getpdo()->exec( $query_comlexNo);
            $db->getpdo()->exec( $query_streetNo);  
            $db->getpdo()->exec( $query_makeContacts);  
            $db->getpdo()->exec( $query_updateContacts);  
            $db->getpdo()->exec( $query_insertContacts);  

        } catch (Exception $ex) {

         dd( $ex->getMessage());
     }


     return $result;
 }
}
