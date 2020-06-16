<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StatusCreated
{
    use SerializesModels;

    /**
     * The job_id returned by Verisure
     *
     * @var string
     */
    public $jobId;

    /**
     * The instance of the parent job, so we
     * can retry the job is something goes wrong server side.
     *
     * @var ShouldQueue
     */
    public $parentJob;

    /**
     * Should notify in case of success
     *
     * @var bool
     */
    public $notify;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $jobId, ShouldQueue $parentJob, bool $notify = true)
    {
        $this->jobId = $jobId;
        $this->parentJob = $parentJob;
        $this->notify = $notify;
    }
}
