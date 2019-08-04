<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Jobs\ActivateHouse;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\ActivateHouseCommand;

class ActivateHouseCommandTest extends TestCase
{
    /**
     * Test command to activate the house
     *
     * @return void
     */
    public function testActivateHouseCommand()
    {
        Queue::fake();
        $this->artisan('verisure:house-activate', ['--mode' => 'full']);
        Queue::assertPushed(ActivateHouse::class);
    }
}
