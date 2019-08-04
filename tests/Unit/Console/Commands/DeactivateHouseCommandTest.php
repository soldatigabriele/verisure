<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Jobs\DeactivateHouse;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\DeactivateHouseCommand;

class DeactivateHouseCommandTest extends TestCase
{
    /**
     * Test command to deactivate the main alarm
     *
     * @return void
     */
    public function testDeactivateHouseCommand()
    {
        Queue::fake();
        $command = new DeactivateHouseCommand;
        $command->handle();
        Queue::assertPushed(DeactivateHouse::class);
    }
}
