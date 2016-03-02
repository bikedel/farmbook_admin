<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Storage;

class LogLockout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Lockout  $event
     * @return void
     */
    public function handle(Lockout $event)
    {
        

    //dd($event);    
    $email = $event->request['email'];
 

    //log 
    $action = 'Login LOCKOUT'; 
    $append =  \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString(). '          '. trim($email).'          '.$action ;
    Storage::append( 'logfile.txt', $append );

    }
}
