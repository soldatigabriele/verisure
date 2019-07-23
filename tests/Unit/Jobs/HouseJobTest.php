<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Jobs\Status;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateHouse;
use Illuminate\Support\Str;
use App\Jobs\DeactivateHouse;
use Illuminate\Support\Facades\Queue;

class HouseJobTest extends TestCase
{
    /**
     * Test ActivateHouse job works fine and dispatches the jobStatus job
     *
     * @return void
     */
    public function testActivateHouse()
    {
        Queue::fake();

        $mock = Mockery::mock(VerisureClient::class);
        foreach (["full", "day", "night"] as $mode) {
            $mock->shouldReceive('activate')->with($mode)->andReturn($jobId = Str::random(20));
            $this->app->instance(VerisureClient::class, $mock);

            // Execute the job
            (new ActivateHouse($mode))->handle($mock);

            // Assert the job to check the status has been dispatched with the correct jobId
            Queue::assertPushedOn('high', Status::class);
            Queue::assertPushed(Status::class, function ($job) use ($jobId) {
                return $job->jobId === $jobId;
            });

        }
    }
    
    /**
     * Test DeactivateHouse job works fine and dispatches the jobStatus job
     *
     * @return void
     */
    public function testDeactivateHouse()
    {
        Queue::fake();

        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('deactivate')->andReturn($jobId = Str::random(20));
        $this->app->instance(VerisureClient::class, $mock);

        // Execute the job
        (new DeactivateHouse)->handle($mock);

        // Assert the job to check the status has been dispatched with the correct jobId
        Queue::assertPushedOn('high', Status::class);
        Queue::assertPushed(Status::class, function ($job) use ($jobId) {
            return $job->jobId === $jobId;
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
