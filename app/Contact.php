<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Database;

class Contact extends Model
{
	protected $connection ;
	protected $table = 'contactsnew';

	public function changeConnection($conn)
	{

		$this->connection = new \App\Database\OTF(['database' => $conn]);
		$this->setConnection($this->connection );

	}
}
