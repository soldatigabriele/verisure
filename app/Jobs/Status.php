<?php

namespace App\Jobs;

use App\VerisureClient;
use App\Jobs\CallWebhook;
use Illuminate\Bus\Queueable;
use App\Status as StatusRecord;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Status implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * List of messages we can process.
     *
     * Garage:
     * OFF    0
     * ON     1
     *
     * House:
     * OFF    0
     * FULL   1
     * DAY    2
     * NIGHT  3
     */
    const SUCCESS = [
        // Actions that return the status on completion
        "Your Secondary Alarm has been activated" => ["garage" => 1],
        "Your Secondary Alarm has been deactivated" => ["garage" => 0],
        "Your Alarm has been deactivated" => ["house" => 0],
        "All of your Alarm's devices have been activated" => ["house" => 1],
        "Your Alarm has been activated in Day Partial mode" => ["house" => 2],
        "Your Alarm has been activated in Night Partial mode" => ["house" => 3],

        // Statuses returned by the status request
        "Your Alarm is activated" => ["house" => 1, "garage" => 0],
        "Your Alarm is deactivated" => ["house" => 0, "garage" => 0],
        "Your Secondary Alarm is activated" => ["house" => 0, "garage" => 1],
        "Your Alarm is activated and your Secondary Alarm" => ["house" => 1, "garage" => 1],
        "Your main Alarm and Secondary Alarm are activated" => ["house" => 1, "garage" => 1],
        "Your Alarm has been activated in Day Partial mode." => ["house" => 2, "garage" => 0],
        "Your Alarm has been activated in Night Partial mode" => ["house" => 3, "garage" => 0],
        "Your Alarm is activated in Day Partial mode and your Secondary Alarm is activated" => ["house" => 2, "garage" => 1],
        "Your Alarm is activated in Night Partial mode and your Secondary Alarm is activated" => ["house" => 3, "garage" => 1],
    ];

    /**
     * List of messages we can't process: manual intervention is required
     */
    const FAIL = [
        "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again." => [],
        "Unable to connect the Alarm because is already connected in another mode." => [],
    ];

    /**
     * Server errors: we can retry the job
     */
    const RETRY = [
        "We have a problem right now, try later" => [],
        "Sorry but we are unable to carry out your request. Please try again later" => [],
        "unable to create new native thread" => [],
        "Invalid session. Please, try again later." => [],
        "Due to a technical issue, the request cannot be processed at present. Please contact Verisure Services" => [],
        "There was a problem communicating with the server" => [],
        "We have had problems identifying you, please end session and log in again." => [],
        "Error 5304. Due to a technical incident we cannot attend to your request. Please, try again in a few minute." => [],
    ];

    /**
     * The job_id returned by Verisure
     *
     * @var string
     */
    public $jobId;

    /**
     * The instance of the parent job, so we
     * can retry the job is something goes wrong server side.
     *
     * @var ShouldQueue
     */
    public $parentJob;

    /**
     * Should notify in case of success
     *
     * @var bool
     */
    public $notify;

    /**
     * Create a new job instance.
     *
     * @param string $jobId The id of the job to check
     * @param ShouldQueue $parentJob The class of the job that created the jobId
     * @param boolean $notify True if you want to notify after the status is updated
     */
    public function __construct($jobId, ShouldQueue $parentJob, $notify = true)
    {
        $this->jobId = $jobId;
        $this->notify = $notify;
        $this->parentJob = $parentJob;
    }

    /**
     * Execute the job.
     *
     * @param VerisureClient $client The instance of VerisureClient
     * @return void
     */
    public function handle(VerisureClient $client)
    {
        $response = $client->jobStatus($this->jobId);
        $this->parseResponse($response, $client);
    }

    /**
     * Parse the response to a standard format
     *
     * @param array $response
     * @param VerisureClient $client The instance of VerisureClient
     * @return void
     */
    protected function parseResponse($response, $client)
    {
        $message = $response["message"];

        // If the request was successful, update the Status record
        if (isset(self::SUCCESS[$message])) {
            // Send a notification if it's enabled in the settings
            if ($this->notify && config('verisure.settings.notifications.status_updated.enabled')) {
                $this->sendNotification(config('verisure.settings.notifications.status_updated.url'), $response);
            }

            $status = StatusRecord::first();
            $status->update(self::SUCCESS[$message]);
            // Update the updated_at field if nothing changed
            $status->touch();
            return;
        }

        // If the request was failed
        if (isset(self::FAIL[$message])) {
            app('log')->error('request failed: ' . $message);
            if ($this->notify && config('verisure.settings.notifications.errors.enabled')) {
                $this->sendNotification(config('verisure.settings.notifications.errors.url'), $response);
            }
            return;
        }

        // In case of server error, we can retry the job and hope the problem is solved
        if (isset(self::RETRY[$message])) {
            // Let's perform a logout and re-push the parent job.
            $client->logout();
            // Push the parent job on the queue to retry the job in a minute.
            if ($this->parentJob->retriesCounter < $this->parentJob->maxRetries) {
                dispatch($this->parentJob)->delay(now()->addMinutes(1));
                return;
            }

            // We want to abort the retry after N many retries.
            app('log')->error('reached max number of retries for job: ' . get_class($this->parentJob));
            if ($this->notify && config('verisure.settings.notifications.errors.enabled')) {
                // Abort the job and notify myself
                $this->sendNotification(config('verisure.settings.notifications.errors.url'), ['status' => 'error', 'message' => 'reached max number of retries for job: ' . get_class($this->parentJob)]);
            }
            return;
        }

        // Handle unmapped messages
        app('log')->error('could not update the status because we got a new unmapped message ("' . $message . '"). Check Jobs/Status@parseResponse()');
    }

    /**
     * Send a push notification using Spatie webhook server package
     *
     * @param string $url
     * @param array $payload
     * @return void
     */
    protected function sendNotification(string $url, array $payload)
    {
        CallWebhook::dispatch($url, $payload)->onQueue("notifications");
    }
}
