<?php

namespace Tests\Unit\VerisureClient;

use Exception;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VerisureClientDeactivateAnnexTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test the annex alarm can be activated
     *
     * @return void
     */
    public function testDeactivate()
    {
        // Create a valid session
        $this->createSession();

        $response = new Response(201, [], json_encode(['job_id' => '4321012345678']));
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $jobId = $client->deactivateAnnex();

        $this->assertEquals('4321012345678', $jobId);
        $this->assertEquals(1, \App\Response::count());
        $this->assertEquals('deactivate garage', \App\Response::latest()->first()->request_type);
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testDeactivationFails()
    {
        $this->expectException(Exception::class);
        // Create a valid session
        $this->createSession();

        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->deactivateAnnex();
    }
}
