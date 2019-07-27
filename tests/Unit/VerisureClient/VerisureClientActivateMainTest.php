<?php

namespace Tests\Unit\VerisureClient;

use Exception;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientActivateMainTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the main alarm can be activated
     *
     * @return void
     */
    public function testActivate()
    {
        // Create a valid session
        $this->createSession();

        $response = new Response(201, [], json_encode(['job_id' => '4321012345678']));
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $jobId = $client->activate('sample');

        $this->assertEquals('4321012345678', $jobId);
        $this->assertEquals(1, \App\Response::count());
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testActivationFails()
    {
        $this->expectException(Exception::class);
        // Create a valid session
        $this->createSession();

        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->activate('sample');
    }
}
