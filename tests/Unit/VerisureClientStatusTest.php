<?php

namespace Tests\Unit;

use Mockery;
use App\Session;
use Carbon\Carbon;
use Tests\TestCase;
use App\VerisureClient;
use Illuminate\Support\Str;
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

        // TODO create the table for the job_statuses
        // $this->assertDatabaseHas('job_statuses', [
        //     'jobId' => '4321012345678',
        //     'requestId' => 'something',
        //     'status' => 1,
        // ]);
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
