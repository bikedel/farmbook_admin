<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Storage;
use Auth;

class LogSuccessfulLogout
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {

         if (Auth::check())
         {      
            // get logged in user email
            $email = $event->user->email;

            //log 
            $action = 'Login Out'; 
            $append =  \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString(). '          '. trim($email).'          '.$action ;
            Storage::append( 'logfile.txt', $append );
         }

     }
}
