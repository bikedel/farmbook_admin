<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Storage;

class LogAuthenticationAttempt
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
     * @param  Attempting  $event
     * @return void
     */
    public function handle(Attempting $event)
    {
        

    //dd($event);
    $email = $event->credentials['email'];
 

    //log 
    $action = 'Login Authentication'; 
    $append =  \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString(). '          '. trim($email).'          '.$action ;
    Storage::append( 'logfile.txt', $append );

    }
}
