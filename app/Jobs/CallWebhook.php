<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CallWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The webhook to be called
     *
     * @var string
     */
    public $webhookUrl;

    /**
     * The body of the request
     *
     * @var array
     */
    public $payload;

    /**
     * Create a new job instance.
     *
     * @param string $url
     * @param array $payload
     * @return void
     */
    public function __construct(string $url, array $payload)
    {
        $this->webhookUrl = $url;
        $this->payload = $payload;
    }

    /**
     * This job will make a post call to a webhook to trigger
     * other actions.
     *
     * @param Client $client The instance of Guzzle Client
     * @return void
     */
    public function handle(Client $client)
    {
        $request = new Request(
            "POST",
            $this->webhookUrl,
            ["Content-Type" => "application/json"],
            json_encode($this->payload)
        );
        $client->send($request);
    }
}
