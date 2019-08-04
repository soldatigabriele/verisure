<?php

namespace App\Jobs;

use Exception;
use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RequestStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $notify;

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
            throw new Exception("Request failed, logging in and trying again");
        }

        if (isset($jobId)) {
            event(new StatusCreated($jobId, $this->notify));
        }
    }
}
