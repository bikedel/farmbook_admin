<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Owner;
use App\Note;
use App\Database;


class Property extends Model
{


    protected $connection ;

    public function changeConnection($conn)
    {

        $this->connection = new \App\Database\OTF(['database' => $conn]);
        $this->setConnection($this->connection );

    }


    /**
     * Get the owner for this property.
     */
    public function owner()
    {

     $foreignKey = 'strIDNumber';
     $localKey  = 'strIdentity';

     $instance = new Owner();
     $instance->setConnection($this->getConnectionName());


     return new HasOne($instance->newQuery(), $this ,$foreignKey, $localKey);
       // return $this->hasOne('App\Owner','strIDNumber' ,'strIdentity' );
 }

    /**
     * Get the note for this property.
     */
    public function note()
    {


     $foreignKey = 'strKey';
     $localKey  = 'strKey';

     $instance = new Note();
     $instance->setConnection($this->getConnectionName());

   //dd($instance->getConnection());


     return new HasOne($instance->newQuery(), $this ,$foreignKey, $localKey);
        //return $this->hasOne('App\Note','strKey' ,'strKey' );
 }



    /**
     * GSearch using like eg:    Property::like('strStreetName', 'Tomas')->get();
     */
    public  function scopeLike($query, $field, $value){
        return $query->where($field, 'LIKE', "%$value%");
    }


     // accessor for amount strAmount
    public function getStrAmountAttribute($number)

    {

         $number = str_replace(',', '', $number);
         $number = str_replace('.', '', $number);
        $length = strlen($number);
        if ($length > 0 ){
          return "R ".number_format($number);
        }
     
    }

     // accessor for bond amount strAmount
    public function getStrBondAmountAttribute($number)

    {

         $number = str_replace(',', '', $number);
         $number = str_replace('.', '', $number);
        $length = strlen($number);
        if ($length > 0 ){
          return "R ".number_format($number);;
        }
     
    }
}
