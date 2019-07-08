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
use App\Exceptions\DeactivationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerisureClientDeactivateMainTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the main alarm can be activated
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
        $jobId = $client->deactivate();

        $this->assertEquals('4321012345678', $jobId);

        // TODO create the table for the job_statuses
        // $this->assertDatabaseHas('job_statuses', [
        //     'jobId' => '4321012345678',
        //     'requestId' => 'something',
        //     'status' => 1,
        // ]);
    }

    /**
     * Test an exception is triggered if the response is not 201
     *
     * @return void
     */
    public function testDeactivationFails()
    {
        $this->expectException(DeactivationException::class);
        // Create a valid session
        $this->createSession();

        $response = new Response(200, []);
        $guzzleClient = $this->mockGuzzle($response);
        $client = new VerisureClient($guzzleClient);
        $client->deactivate();
    }

    /**
     * Create a valid session
     *
     * @return Session
     */
    public function createSession(): Session
    {
        return Session::create([
            'csrf' => Str::random(20),
            'value' => Str::random(20),
            'expires' => Carbon::tomorrow(),
        ]);

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
