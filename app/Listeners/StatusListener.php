<?php

namespace App\Listeners;

use App\Jobs\JobStatus;
use App\Events\StatusCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        JobStatus::dispatch($event->jobId);
    }
}
