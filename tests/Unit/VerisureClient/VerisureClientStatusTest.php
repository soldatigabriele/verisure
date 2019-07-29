<?php

namespace Tests\Unit\VerisureClient;

use Exception;
use Carbon\Carbon;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Status test.
     *
     * @return void
     */
    public function testStatus()
    {
        $this->createSession();
        $response = new Response(201, [], json_encode(['job_id' => '4321012345678']));
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $jobId = $client->status();

        $this->assertEquals('4321012345678', $jobId);
    }

    /**
     * Test the session is updated when a request is made
     *
     * @return void
     */
    public function testSessionUpdated()
    {
        // Set the test time now() to the session time, so we can create a cookie that will expire 20 minutes after
        Carbon::setTestNow(Carbon::parse('Thu, 04-Jul-2019 20:00:00 GMT'));
        $session = $this->createSession();

        // This session cookie will expire 40 minutes from now, we want to update the one we have in the DB
        $response = new Response(201, [
            'Set-Cookie' => '_session_id=' . $session->value . '; path=/; expires=Thu, 04-Jul-2019 20:40:00 GMT; HttpOnly'], json_encode(['job_id' => '4321012345678',
        ]));
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $jobId = $client->status();

        $this->assertEquals($session->created_at->addMinutes(40), $session->fresh()->expires);
    }

    /**
     * Test an Exception is thown if the status code is not 201
     *
     * @return void
     */
    public function testStatusFails()
    {
        $this->createSession();
        $this->expectException(Exception::class);
        // The VerisureClient expects a status 201 when asking for a Status
        $response = new Response(200, [], json_encode(['job_id' => 'ok']));
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->status();
    }
}
