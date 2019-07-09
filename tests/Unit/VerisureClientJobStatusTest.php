<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\JobStatusException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientJobStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the main alarm can be activated
     *
     * @return void
     */
    public function testJobStatus()
    {
        // Create a valid session
        $this->createSession();

        $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "working", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => []]));
        $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "queued", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => []]));
        $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "completed", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => [], "message" => ["status" => 0, "message" => "Your Alarm has been deactivated"]]));
        $guzzleClient = $this->mockGuzzle($responses);
        $client = new VerisureClient($guzzleClient);

        $message = $client->jobStatus(Str::random(20));

        $this->assertEquals('completed', $message['status']);
    }

    /**
     * Test an exception is triggered if the status in the response is not 'completed' or 'working'
     *
     * @return void
     */
    public function testActivationFailsForWrongStatus()
    {
        $this->expectException(JobStatusException::class);
        // Create a valid session
        $this->createSession();

        $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "error", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => [], "message" => ["status" => 0, "message" => "Your Alarm has been deactivated"]]));
        $guzzleClient = $this->mockGuzzle($responses);
        $client = new VerisureClient($guzzleClient);
        $client->jobStatus(Str::random(20));
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testActivationFailsForTooManyAttempts()
    {
        $this->expectException(JobStatusException::class);
        // Create a valid session
        $this->createSession();

        for ($i = 0; $i < config('verisure.status_job.max_calls') + 1; $i++) {
            $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "working", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => []]));
        }
        $guzzleClient = $this->mockGuzzle($responses);
        $client = new VerisureClient($guzzleClient);
        $client->jobStatus(Str::random(20));
    }

    /**
     * TearDown the test
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
