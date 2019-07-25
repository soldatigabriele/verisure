<?php

namespace Tests\Unit\VerisureClient;

use Mockery;
use App\Record;
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
        $responses[] = new Response(200, [], json_encode(["time" => 1562586383, "status" => "completed", "uuid" => "cda28c2bfa31eeb833d2077f3fbf7695", "options" => [], "message" => ["status" => 0, "message" => $alarmMessage = "Your Alarm has been deactivated"]]));
        $guzzleClient = $this->mockGuzzle($responses);
        $client = new VerisureClient($guzzleClient);

        $message = $client->jobStatus(Str::random(20));

        $this->assertEquals('completed', $message['status']);
        $this->assertEquals(3, \App\Response::count());
        $this->assertEquals($alarmMessage, Record::first()->body);
    }

    /**
     * Test a failed attempt to activate alarm (window open)
     *
     * @return void
     */
    public function testJobStatusFailsOpenWindow()
    {
        // Create a valid session
        $this->createSession();

        $responses[] = new Response(200, [], json_encode(["status" => "working"]));
        // Note: the message is on a different level in case of failed response
        $responses[] = new Response(200, [], json_encode(["status" => "failed", "message" => $alarmMessage = "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again."]));
        $guzzleClient = $this->mockGuzzle($responses);
        $client = new VerisureClient($guzzleClient);

        $message = $client->jobStatus(Str::random(20));

        $this->assertEquals('failed', $message['status']);
        $this->assertEquals(2, \App\Response::count());
        $this->assertEquals($alarmMessage, Record::first()->body);
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
        $this->assertEquals(1, \App\Response::count());
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
