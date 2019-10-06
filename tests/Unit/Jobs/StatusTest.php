<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Jobs\Status;
use App\VerisureClient;
use GuzzleHttp\Psr7\Response;
use App\Status as StatusRecord;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StatusTest extends TestCase
{
    use DatabaseMigrations;

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
        config()->set(['verisure.settings.notifications.enabled' => false]);
        $job = new Status('job-id-test');
        $job->handle($verisureClient, $notificationSystem);
        $this->addToAssertionCount(1);
    }

    /**
     * Test Status (the record) is updated by the Status job
     *
     * @return void
     */
    public function testStatusRecordUpdated()
    {
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'Your Secondary Alarm has been activated', 'status' => 'ok']);
        $notificationSystem = $this->mockGuzzle(new Response(200, [], json_encode(['status' => 'ok'])));
        $job = new Status('job-id-test');
        $job->handle($verisureClient, $notificationSystem);
        $status = StatusRecord::first();
        $this->assertEquals(1, $status->garage);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
