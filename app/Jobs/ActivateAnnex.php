<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ActivateAnnex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Should notify in case of success
     *
     * @var bool
     */
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
        $jobId = $client->activateAnnex();
        event(new StatusCreated($jobId, (new ActivateAnnex($this->notify)), $this->notify));
    }
}
