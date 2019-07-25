<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Jobs\RequestStatus;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\RequestStatusCommand;

class RequestStatusCommandTest extends TestCase
{
    /**
     * Test command to get the alarm status
     *
     * @return void
     */
    public function testRequestStatusCommand()
    {
        Queue::fake();
        $command = new RequestStatusCommand;
        $command->handle();
        Queue::assertPushedOn('high', \App\Jobs\RequestStatus::class);
    }
}
