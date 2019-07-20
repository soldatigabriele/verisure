<?php

namespace Tests\Unit\VerisureClient;

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

class VerisureClientDeactivateAnnexTest extends TestCase
{
    use RefreshDatabase;

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
        $client->deactivateAnnex();
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
