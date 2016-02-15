<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Database;


class Owner extends Model
{

	protected $connection ;

	public function changeConnection($conn)
	{

		$this->connection = new \App\Database\OTF(['database' => $conn]);
		$this->setConnection($this->connection );

	}


     /**
     * Get the properties for this owner.
     */
    public function properties()
    {

     $foreignKey = 'strIDNumber';
     $localKey  = 'strIdentity';

     $instance = new Property();
     $instance->setConnection($this->getConnectionName());


     return new HasOne($instance->newQuery(), $this ,$foreignKey, $localKey);

        //return $this->hasMany('App\Properties', 'strIDNumber', 'strIdentity');
    }
}
