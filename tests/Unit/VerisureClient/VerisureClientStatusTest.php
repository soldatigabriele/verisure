<?php

namespace Tests\Unit\VerisureClient;

use Mockery;
use Tests\TestCase;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use App\Exceptions\StatusException;
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
     * Test a StatusException is thown if the status code is not 201
     *
     * @return void
     */
    public function testStatusFails()
    {
        $this->expectException(StatusException::class);

        $this->createSession();

        // The VerisureClient expects a status 201 when asking for a Status
        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->status();
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
