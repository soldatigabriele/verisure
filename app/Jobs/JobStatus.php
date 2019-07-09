<?php

namespace App\Jobs;

use App\VerisureClient;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jobId = null)
    {
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(VerisureClient $client, GuzzleClient $guzzle)
    {
        $response = $client->jobStatus($this->jobId);
        $this->guzzle = $guzzle;
        $response = $this->sendNotification($response);
        // TODO Log the response
    }

    /**
     * Send a push notification
     *
     * @param array $response
     * @return void
     */
    protected function sendNotification(array $response)
    {
        info($response['message']);
        $url = 'http://wirepusher.com/send?id=mpgNm&title=' . $response['status'] . '&message=' . $response['message'] . '&type=alarm_activated';
        $notification = new Request("POST", $url);
        $response = $this->guzzle->send($notification);
        return json_decode($response->getBody()->getContents(), true);
    }
}
