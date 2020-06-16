<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ActivateHouse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * What mode the alarm should activate:
     * full, day, night
     *
     * @var string
     */
    public $mode;

    /**
     * Should notify in case of success
     *
     * @var bool
     */
    public $notify;

    /**
     * Create a new job instance.
     *
     * @param string $mode The request mode, can be full, day or night
     * @return void
     */
    public function __construct($mode, $notify = true)
    {
        $this->mode = $mode;
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
        $jobId = $client->activate($this->mode);
        $parentJob = (new ActivateHouse($this->mode, $this->notify));
        event(new StatusCreated($jobId, $parentJob, $this->notify));
    }
}
