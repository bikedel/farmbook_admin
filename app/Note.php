<?php

namespace App;

use App\Database;
use App\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{

    protected $connection;

    public function changeConnection($conn)
    {

        $this->connection = new \App\Database\OTF(['database' => $conn]);
        $this->setConnection($this->connection);

    }

    /**
     * Get the properties for this note.
     */
    public function properties()
    {

        $foreignKey = 'strKey';
        $localKey   = 'strKey';

        $instance = new Property();
        $instance->setConnection($this->getConnectionName());

        return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);

        //return $this->HasOne('App\Property', 'strKey', 'strKey');
    }

}
