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


    // accessor for Home telephone number
    public function getStrHomePhoneNoAttribute($phone)

    {
        $length = strlen($phone);
        if ($length == 9){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(0".substr($phone,0,2).") ".substr($phone,2,3)."-".substr($phone,5);
        }
        if ($length == 10){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(".substr($phone,0,3).") ".substr($phone,3,3)."-".substr($phone,6);
        }
        return $phone;
     
    }

    // mutator for Home telephone number
    public function setStrHomePhoneNoAttribute($phone)

    {
       

        $phone = str_replace('-', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace(' ', '', $phone);

        $this->attributes['strHomePhoneNo'] = $phone;
    }

     // accessor for Work telephone number
    public function getStrWorkPhoneNoAttribute($phone)

    {
        $length = strlen($phone);
        if ($length == 9){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(0".substr($phone,0,2).") ".substr($phone,2,3)."-".substr($phone,5);
        }
        if ($length == 10){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(".substr($phone,0,3).") ".substr($phone,3,3)."-".substr($phone,6);
        }
        return $phone;
     
    }

    // mutator for Work telephone number
    public function setStrWorkPhoneNoAttribute($phone)

    {
       

        $phone = str_replace('-', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace(' ', '', $phone);

        $this->attributes['strWorkPhoneNo'] = $phone;
    }

     // accessor for Cell telephone number
    public function getStrCellPhoneNoAttribute($phone)

    {
        $length = strlen($phone);
        if ($length == 9){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(0".substr($phone,0,2).") ".substr($phone,2,3)."-".substr($phone,5);
        }
        if ($length == 10){
          $phone = preg_replace("/[^0-9]/", "", $phone);
          $phone = "(".substr($phone,0,3).") ".substr($phone,3,3)."-".substr($phone,6);
        }
        return $phone;
     
    }

    // mutator forCell telephone number
    public function setStrCellPhoneNoAttribute($phone)

    {
       

        $phone = str_replace('-', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace(' ', '', $phone);

        $this->attributes['strCellPhoneNo'] = $phone;
    }


}
