<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Jobs\Status;
use App\VerisureClient;
use App\Jobs\RequestStatus;
use Illuminate\Support\Facades\Queue;

class RequestStatusTest extends TestCase
{
    /**
     * Test RequestStatus job
     *
     * @return void
     */
    public function testRequestStatusJob()
    {
        Queue::fake();
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('status')->once()->andReturn($jobId = 'job-id');
        $job = new RequestStatus;
        $job->handle($verisureClient);

        // Assert the job to check the status has been dispatched with the correct jobId
        Queue::assertPushedOn('high', Status::class);
        Queue::assertPushed(Status::class, function ($job) use ($jobId) {
            return $job->jobId === $jobId;
        });
    }

    /**
     * Test error 500 from server
     *
     * @return void
     */
    public function testServerError()
    {
        $this->expectException(Exception::class);
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('status')->once()->andThrow(Exception::class)
        // If the job fails, let's try to logout and login again
            ->shouldReceive('logout')->once()
            ->shouldReceive('login')->once();

        $job = new RequestStatus;
        $job->handle($verisureClient);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
