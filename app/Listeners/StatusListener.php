<?php

namespace App\Listeners;

use App\Jobs\Status;
use App\Events\StatusCreated;

class StatusListener
{
    /**
     * Handle the event.
     *
     * @param  StatusCreated  $event
     * @return void
     */
    public function handle(StatusCreated $event)
    {
        // Push on the queue the job to check the status of
        // the connection and notify the user.
        // We pass the parent job to retry it in case of server failure.
        Status::dispatch($event->jobId, $event->parentJob, $event->notify)->onQueue('high');
    }
}
