<?php

namespace Tests\Unit\Console\Commands;

use Mockery;
use App\Record;
use Tests\TestCase;
use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\RequestStatusCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        Queue::assertPushedOn('high', \App\Jobs\Status::class);
    }
}
