<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $jobId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(bool $notify = false)
    {
        $this->notify = $notify;
    }

    /**
     * Execute the job.
     *
     * @param VerisureClient $client The instance of VerisureClient
     * @return void
     */
    public function handle(VerisureClient $client)
    {
        $jobId = $client->status();
        event(new StatusCreated($jobId, $this->notify));
    }
}
