<?php

namespace Tests\Feature;

use Mockery;
use App\Session;
use Tests\TestCase;
use GuzzleHttp\Client;
use App\Jobs\JobStatus;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test jobStatus job
     *
     * @return void
     */
    public function testJobStatus()
    {
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'test', 'status' => 'ok']);
        $wirePusher = $this->mockGuzzle(new Response(200, [], json_encode(['status' => 'ok'])));
        // ->andReturn(["status" => "completed", "message" => "Alarm activated"]);
        $job = new JobStatus('job-id-test');
        $job->handle($verisureClient, $wirePusher);
        $this->addToAssertionCount(1);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
