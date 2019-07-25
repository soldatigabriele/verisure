<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class StatusCreated
{
    use SerializesModels;

    public $jobId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($jobId)
    {
        $this->jobId = $jobId;
    }
}
