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

    public $jobId;
    public $notify;

    /**
     * Create a new job instance.
     *
     * @param string $jobId The id of the job to check
     * @return void
     */
    public function __construct($jobId = null, $notify = true)
    {
        $this->jobId = $jobId;
        $this->notify = $notify;
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
        // Send a notification if it's enabled in the settings
        if (config('verisure.settings.notifications.status_updated.enabled') && $this->notify) {
            $this->sendNotification(config('verisure.settings.notifications.status_updated.url'), $response);
        }
        $this->parseResponse($response["message"]);
    }

    /**
     * Parse the response to a standard format
     *
     * @param string $message
     * @return void
     */
    protected function parseResponse($message)
    {
        /**
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
        $cases = [
            // Actions
            "Your Secondary Alarm has been activated" => ["garage" => 1],
            "Your Secondary Alarm has been deactivated" => ["garage" => 0],
            "Your Alarm has been deactivated" => ["house" => 0],
            "All of your Alarm's devices have been activated" => ["house" => 1],
            "Your Alarm has been activated in DAY PARTIAL mode" => ["house" => 2],
            "Your Alarm has been activated in NIGHT PARTIAL mode." => ["house" => 3],

            // Statuses
            "Your Alarm is activated" => ["house" => 1, "garage" => 0],
            "Your Alarm is deactivated" => ["house" => 0, "garage" => 0],
            "Your Secondary Alarm is activated" => ["house" => 0, "garage" => 1],
            "Your Alarm is activated and your Secondary Alarm" => ["house" => 1, "garage" => 1],
            "Your Alarm has been activated in DAY PARTIAL mode." => ["house" => 2, "garage" => 0],
            "Your Alarm has been activated in NIGHT PARTIAL mode." => ["house" => 3, "garage" => 0],
            "Your Alarm is activated in DAY PARTIAL mode and your SECONDARY Alarm" => ["house" => 2, "garage" => 1],
            "Your Alarm is activated in NIGHT PARTIAL mode and the SECONDARY alarm" => ["house" => 3, "garage" => 1],

            // Errors
            "Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again." => [],

            "Sorry but we are unable to carry out your request. Please try again later" => [],
            "unable to create new native thread" => [],
            "Due to a technical issue, the request cannot be processed at present. Please contact Verisure Services" => [],
            "There was a problem communicating with the server" => [],
            "We have had problems identifying you, please end session and log in again." => [],
            "Error 5304. Due to a technical incident we cannot attend to your request. Please, try again in a few minute." => [],
        ];

        // Update the Status record
        if (isset($cases[$message])) {
            $status = StatusRecord::first();
            $status->update($cases[$message]);
            // Update the updated_at field if nothing changed
            $status->touch();
        } elseif (config('verisure.settings.notifications.errors.enabled')) {
            app('log')->warning('could not update the status because we got a new unmapped message ("' . $message . '"). Check Jobs/Status@parseResponse()');
            $this->sendNotification(config('verisure.settings.notifications.error.url'), ['status' => 'warning', 'message' => 'undefined message: check the logs for more info']);
        }
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
