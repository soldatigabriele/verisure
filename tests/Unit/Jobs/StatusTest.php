<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use App\Jobs\Status;
use App\VerisureClient;
use App\Jobs\CallWebhook;
use Illuminate\Support\Str;
use TiMacDonald\Log\LogFake;
use Illuminate\Bus\Queueable;
use App\Status as StatusRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StatusTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->parentJob = new class implements ShouldQueue
        {
            use Queueable;
            public $retriesCounter = 0;
            public $maxRetries = 1;
        };
        Queue::fake();
        Log::swap(new LogFake);
    }

    /**
     * Test Status job Notification is sent if the status is success
     *
     * @return void
     */
    public function testStatusJobNotificationIsSent()
    {
        config()->set([
            'verisure.settings.notifications.status_updated.enabled' => true,
            'verisure.settings.notifications.status_updated.url' => $url = "http://localhost",
        ]);

        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => $message = 'Your Secondary Alarm has been activated', 'status' => 'ok']);
        (new Status('job-id-test', $this->parentJob, true))->handle($verisureClient);

        // Check that the job to call the webhook is pushed
        Queue::assertPushed(CallWebhook::class, function (CallWebhook $job) use ($url, $message) {
            $this->assertEquals($url, $job->webhookUrl);
            // The payload contains the message from Verisure server
            $this->assertContains($message, $job->payload);
            return true;
        });
    }

    /**
     * Test Status job Notification is not sent if the status is failed or retry
     *
     * @return void
     */
    public function testStatusJobNotificationIsNotSent()
    {
        config()->set([
            'verisure.settings.notifications.status_updated.enabled' => true,
            'verisure.settings.notifications.status_updated.url' => $url = "http://localhost",
        ]);

        $messages = [
            "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again.",
            "Sorry but we are unable to carry out your request. Please try again later",
        ];

        foreach ($messages as $message) {

            $verisureClient = Mockery::mock(VerisureClient::class);
            $verisureClient->shouldReceive('logout')
                ->shouldReceive('jobStatus')
                ->with('job-id-test')->once()
                ->andReturn(['message' => $message, 'status' => 'ok']);
            (new Status('job-id-test', $this->parentJob, true))->handle($verisureClient);
        }

        // Check that the job to call the webhook is pushed
        Queue::assertNotPushed(CallWebhook::class, function (CallWebhook $job) use ($url, $message) {
            $this->assertEquals($url, $job->webhookUrl);
            return true;
        });
    }

    /**
     * Test log notification when no message is matched
     *
     * @return void
     */
    public function testNotificationWhenMessageNotMatched()
    {
        config()->set(['verisure.settings.notifications.enabled' => false]);

        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'test', 'status' => 'ok']);
        (new Status('job-id-test', $this->parentJob, true))->handle($verisureClient);

        // We don't expect the notification to be triggered
        Queue::assertNotPushed(CallWebhook::class);

        Log::assertLogged('error', function ($message) {
            return Str::contains($message, 'could not update the status because we got a new unmapped message');
        });
    }

    /**
     * Test notification can be disabled
     *
     * @return void
     */
    public function testNotificationDisabledFromConstructor()
    {
        config()->set(['verisure.settings.notifications.enabled' => true]);

        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'Your Secondary Alarm has been activated', 'status' => 'ok']);
        // We set the $notify to false, so we don't expect the webhook to be called
        (new Status('job-id-test', $this->parentJob, false))->handle($verisureClient);

        // We don't expect the notification to be triggered
        Queue::assertNotPushed(CallWebhook::class);
    }

    /**
     * Test Status (the record) is updated by the Status job
     *
     * @return void
     */
    public function testStatusRecordUpdated()
    {
        $this->assertEquals(0, StatusRecord::first()->garage);
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'Your Secondary Alarm has been activated', 'status' => 'ok']);
        (new Status('job-id-test', $this->parentJob, false))->handle($verisureClient);

        $this->assertEquals(1, StatusRecord::first()->garage);
    }

    /**
     * Test Status (the record) is updated by the Status job even if
     * the status in not changed.
     *
     * @return void
     */
    public function testStatusRecordUpdatedIfMessageIsNotChanged()
    {
        // Update the status of updated_at of the StatusRecord in the past
        Carbon::setTestNow(Carbon::now()->subDays(10));
        $status = StatusRecord::first();
        $status->touch();
        // Reset to the present
        Carbon::setTestNow();

        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => 'Your Alarm has been deactivated', 'status' => 'ok']);
        (new Status('job-id-test', $this->parentJob, false))->handle($verisureClient);

        // Check that the status is not changed, but the timestamps are updated
        $status->refresh();
        $this->assertEquals(0, $status->house);
        $this->assertEquals(0, $status->garage);
        $this->assertTrue($status->updated_at->isToday());
    }

    /**
     * Test the request fails for an open window/door
     *
     * @return void
     */
    public function testRequestFailed()
    {
        $verisureClient = Mockery::mock(VerisureClient::class);
        $verisureClient->shouldReceive('jobStatus')->with('job-id-test')->once()->andReturn(['message' => $message = 'Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again.', 'status' => 'ok']);
        (new Status('job-id-test', $this->parentJob, false))->handle($verisureClient);

        $this->assertEquals(0, StatusRecord::first()->garage);
        $this->assertEquals(0, StatusRecord::first()->house);
        Log::assertLogged('error', function ($msg) use ($message) {
            return Str::contains($msg, 'request failed: ' . $message);
        });
    }

    /**
     * Test the request failed for server problems, we want to retry the job.
     *
     * @return void
     */
    public function testRetryServerErrorRequest()
    {
        config()->set([
            'verisure.settings.notifications.status_updated.enabled' => true,
            'verisure.settings.notifications.status_updated.url' => "http://localhost",
        ]);

        $verisureClient = Mockery::mock(VerisureClient::class);
        $message = "Sorry but we are unable to carry out your request. Please try again later";
        $verisureClient->shouldReceive('jobStatus')
            ->with('job-id-test')->once()
            ->andReturn(['message' => $message, 'status' => 'ok']);

        $verisureClient->shouldReceive('logout')->times(1);
        (new Status('job-id-test', $this->parentJob, true))->handle($verisureClient);
        Queue::assertPushed(get_class($this->parentJob));
    }

    /**
     * Test the request failed for server problems, we want to retry up to the max tries.
     *
     * @return void
     */
    public function testRetryServerErrorRequestMaxRetryReached()
    {
        config()->set([
            'verisure.settings.notifications.status_updated.enabled' => true,
            'verisure.settings.notifications.status_updated.url' => "http://localhost",
        ]);

        $verisureClient = Mockery::mock(VerisureClient::class);
        $message = "Sorry but we are unable to carry out your request. Please try again later";
        $verisureClient->shouldReceive('jobStatus')
            ->with('job-id-test')->once()
            ->andReturn(['message' => $message, 'status' => 'ok']);

        $verisureClient->shouldReceive('logout')->times(1);
        // Set the max retries to 0, so we don't push the job again.
        $this->parentJob->maxRetries = 0;
        (new Status('job-id-test', $this->parentJob, true))->handle($verisureClient);
        Queue::assertNotPushed(get_class($this->parentJob));

        Log::assertLogged('error', function ($message) {
            return Str::contains($message, 'reached max number of retries for job:');
        });
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
