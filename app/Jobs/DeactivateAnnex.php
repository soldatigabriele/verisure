<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeactivateAnnex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    
    public $notify;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(bool $notify = true)
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
        $jobId = $client->deactivateAnnex();
        event(new StatusCreated($jobId, $this->notify));
    }
}
