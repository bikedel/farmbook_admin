<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Database;

class Complex extends Model
{
	protected $connection ;

	public function changeConnection($conn)
	{

		$this->connection = new \App\Database\OTF(['database' => $conn]);
		$this->setConnection($this->connection );

	}
}
