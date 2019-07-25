<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class StatusCreated
{
    use SerializesModels;

    public $jobId;

    public $notify;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $jobId, bool $notify = true)
    {
        $this->jobId = $jobId;
        $this->notify = $notify;
    }
}
