<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Farmbook extends Model
{

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function suburbs()
    {
        return $this->belongsToMany('App\Suburb');
    }

}
