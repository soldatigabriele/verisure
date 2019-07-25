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

    /**
     * Test notification can be disabled
     *
     * @return void
     */
    public function testNotification()
    {
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'test', 'status' => 'ok']);
        // The test doesn't fail, as we don't expect Guzzle to try and make the call to notify
        $notificationSystem = $this->mockGuzzle([]);
        config()->set(['verisure.notification.enabled' => false]);
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
