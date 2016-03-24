<?php

namespace App;

use DB;
use Exception;
use File;

class CsvFileUpdater
{
    /**
     * Import method used for saving file and importing it using a database query
     *
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $csv_import
     * @return int number of lines imported
     */
    public function update($csv_import, $database)
    {
        // Save file to temp directory
        $moved_file = $this->moveFile($csv_import);

        // Normalize line endings
        $normalized_file = $this->normalize($moved_file);

        // Import contents of the file into database
        $result = $this->importFileContents($normalized_file, $database);

        return $result;
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
        $destination_directory = storage_path('updates/tmp');
        if (is_dir($destination_directory)) {
            //chmod($destination_directory, 0755);
        } else {

            //mkdir($destination_directory, 0755, true);
        }

        // Get file's original name
        $original_file_name = $csv_import->getClientOriginalName();

        if (is_file($destination_directory . '/' . $original_file_name)) {

            File::delete($destination_directory . '/' . $original_file_name);
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
    private function importFileContents($file_path, $database)
    {

        $chost    = config('database.connections.mysql.host');
        $cuser    = config('database.connections.mysql.username');
        $cpass    = config('database.connections.mysql.password');
        $user     = $cuser;
        $password = $cpass;
        $host     = $chost;
        $dbname   = $database;
        $dsn      = 'mysql:dbname=test1;';

        // connect to tmp database
        $otf = new \App\Database\OTF(['database' => $dbname]);
        $db  = DB::connection($dbname);

        // delete records and import
        $query_delete = ('TRUNCATE TABLE updates');

        $key = "CONCAT(numErf,'-', numPortion)";

        if (strpos($database, '_FH') !== false) {
            $key = "CONCAT(numErf,'-', numPortion)";
        }
        if (strpos($database, '_ST') !== false) {
            $key = "CONCAT(strComplexName,' ', strComplexNo)";
        }

//dd($key);

        $query = sprintf("LOAD DATA INFILE '%s' REPLACE INTO TABLE updates
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
        SET strKey = " . $key, addslashes($file_path));

        $query_blankid  = ('UPDATE IGNORE updates SET strIdentity = strOwners WHERE strIdentity = ""');
        $query_blankid2 = ('UPDATE IGNORE updates SET strIdentity = REPLACE(strTitleDeed,"/","") WHERE strIdentity = ""');
        $query_blankid3 = ('UPDATE IGNORE updates SET strOwners = REPLACE(strTitleDeed,"/","") WHERE strOwners = ""');

        try {
            //delete
            $db->getpdo()->exec($query_delete);

            // $db->getpdo()->exec( $query_delete5);

            //import updates
            $result = $db->getpdo()->exec($query);

            // fix missing owner,id
            $db->getpdo()->exec($query_blankid);
            $db->getpdo()->exec($query_blankid2);
            $db->getpdo()->exec($query_blankid3);

        } catch (Exception $ex) {

            dd($ex->getMessage());
        }

        return $result;
    }
}
