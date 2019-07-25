<?php

namespace Tests\Unit\Http\Controllers;

use Mockery;
use App\Record;
use Tests\TestCase;
use App\VerisureClient;
use App\Events\StatusCreated;
use App\Console\Commands\Status;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusTest extends TestCase
{
    /**
     * Test command to get the alarm status
     *
     * @return void
     */
    public function testCommand()
    {
        // $this->expectsEvents(StatusCreated::class);
        Queue::fake();
        $client = Mockery::mock(VerisureClient::class);
        $client->shouldReceive('status')->once()->andReturn($jobId = 'job-id');
        $command = new Status($client);
        $command->handle();
        Queue::assertPushedOn('high', \App\Jobs\Status::class);
        Queue::assertPushed(\App\Jobs\Status::class, function ($job) use ($jobId) {
            return $job->jobId === $jobId;
        });

    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
