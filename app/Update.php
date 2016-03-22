<?php

namespace App;

use App\Database;
use Illuminate\Database\Eloquent\Model;

class Update extends Model
{

    protected $connection;

    public function changeConnection($conn)
    {

        $this->connection = new \App\Database\OTF(['database' => $conn]);
        $this->setConnection($this->connection);

    }

}
