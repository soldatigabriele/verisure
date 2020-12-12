<?php

namespace App\Jobs;

use Exception;
use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use App\Exceptions\RetryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestStatus implements ShouldQueue
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
    public $maxRetries = 2;

    /**
     * Create a new job instance.
     *
     * @param bool $notify
     * @param int $retriesCounter
     * @return void
     */
    public function __construct(bool $notify = false, int $retriesCounter = 1)
    {
        $this->notify = $notify;
        $this->retriesCounter = $retriesCounter;
    }

    /**
     * Execute the job to request the status of the alarm.
     *
     * @param VerisureClient $client The instance of VerisureClient
     * @return void
     */
    public function handle(VerisureClient $client)
    {
        try {
            $jobId = $client->status();
        } catch (Exception $e) {
            $client->logout();
            $client->login();
            throw new RetryException("Request failed, logging in and trying again");
        }

        if (isset($jobId)) {
            $parentJob = new RequestStatus(false, $this->retriesCounter + 1);
            event(new StatusCreated($jobId, $parentJob, $this->notify));
        }
    }
}
