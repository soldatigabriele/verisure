<?php

namespace App\Listeners;

use App\Jobs\Status;
use App\Events\StatusCreated;

class StatusListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(StatusCreated $event)
    {
        // Push on the queue the job to check the status of
        // the connection and notify the user
        Status::dispatch($event->jobId, $event->notify)->onQueue('high');
    }
}
