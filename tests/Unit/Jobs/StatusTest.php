<?php

namespace Tests\Unit\Jobs;

use Mockery;
use App\Session;
use Tests\TestCase;
use App\Jobs\Status;
use GuzzleHttp\Client;
use App\VerisureClient;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusTest extends TestCase
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
        $notificationSystem = $this->mockGuzzle(new Response(200, [], json_encode(['status' => 'ok'])));
        $job = new Status('job-id-test');
        $job->handle($verisureClient, $notificationSystem);
        $this->addToAssertionCount(1);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
