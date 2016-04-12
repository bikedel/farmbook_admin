<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Attempting;
use Request;
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
        $ip    = Request::ip();

        //log
        $action = 'Login Authentication';
        $append = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',              IP:   ' . $ip;
        Storage::append('logfile.txt', $append);

    }
}
