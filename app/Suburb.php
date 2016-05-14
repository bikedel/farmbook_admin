<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Suburb extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function farmbooks()
    {
        return $this->belongsToMany('App\Farmbook');
    }

}
