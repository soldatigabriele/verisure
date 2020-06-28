<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeactivateHouse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Should notify in case of success
     *
     * @var bool
     */
    public $notify;

    /**
     * The retry counter keeps track of how
     * many times a job has been retried
     *
     * @var integer
     */
    public $retriesCounter = 1;

    /**
     * The max retry indicates how many retries
     * we want to do before giving up on the job.
     *
     * @var integer
     */
    public $maxRetries = 10;

    /**
     * Create a new job instance.
     *
     * @param bool $notify
     * @param int $retriesCounter
     * @return void
     */
    public function __construct($notify = true, int $retriesCounter = 1)
    {
        $this->notify = $notify;
        $this->retriesCounter = $retriesCounter;
    }

    /**
     * Execute the job.
     *
     * @param VerisureClient $client The instance of VerisureClient
     * @return void
     */
    public function handle(VerisureClient $client)
    {
        $jobId = $client->deactivate();
        $parentJob = (new DeactivateHouse($this->notify, $this->retriesCounter + 1));
        event(new StatusCreated($jobId, $parentJob, $this->notify));
    }
}
