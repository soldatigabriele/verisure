<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Jobs\Status;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;

class StatusTest extends TestCase
{
    /**
     * Test Status job
     *
     * @return void
     */
    public function testStatusJob()
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
