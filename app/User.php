<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Farmbook;
class User extends Authenticatable
{


    protected $casts = [
    'is_Admin' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'admin', 'active', 'farmbook',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


   public function farmbooks()
    {
        return $this->belongsToMany('App\Farmbook');
    }

   public function getDatabase()
    {

        $data = Farmbook::where('id','=',$this->farmbook)->first()->database;
        return $data;
    }

   public function getDatabaseName()
    {

        $data = Farmbook::where('id','=',$this->farmbook)->first()->name;
        return $data;
    }
    
    public function isAdmin()
    {

         return $this->admin; // this looks for an admin column in your users table
    }
}
