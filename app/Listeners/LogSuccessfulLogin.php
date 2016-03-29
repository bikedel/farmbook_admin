<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Request;
use Storage;

class LogSuccessfulLogin
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {

        $email = $event->user->email;
        $ip    = Request::ip();

        //log
        $action = 'LOGIN';
        $append = \Carbon\Carbon::now('Africa/Johannesburg')->toDateTimeString() . ',          ' . trim($email) . ',          ' . $action . ',              IP:   ' . $ip;
        Storage::append('logfile.txt', $append);

    }
}
