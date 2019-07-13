<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Jobs\Status;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateAnnex;
use Illuminate\Support\Str;
use App\Jobs\DeactivateAnnex;
use Illuminate\Support\Facades\Queue;

class AnnexTest extends TestCase
{

    /**
     * Test ActivateAnnex job works fine and dispatches the jobStatus job
     *
     * @return void
     */
    public function testActivateAnnex()
    {
        Queue::fake();

        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('activateAnnex')->andReturn($jobId = Str::random(20));
        $this->app->instance(VerisureClient::class, $mock);

        (new ActivateAnnex)->handle($mock);

        // Assert the job to check the status has been dispatched with the correct jobId
        Queue::assertPushed(Status::class, function ($job) use ($jobId) {
            return $job->jobId === $jobId;
        });
    }

    /**
     * Test DeactivateAnnex job works fine and dispatches the jobStatus job
     *
     * @return void
     */
    public function testDeactivateAnnex()
    {
        Queue::fake();

        $mock = Mockery::mock(VerisureClient::class);
        $mock->shouldReceive('deactivateAnnex')->andReturn($jobId = Str::random(20));
        $this->app->instance(VerisureClient::class, $mock);

        // Execute the job
        (new DeactivateAnnex)->handle($mock);

        // Assert the job to check the status has been dispatched with the correct jobId
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
