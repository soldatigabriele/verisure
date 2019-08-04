<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Jobs\DeactivateAnnex;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\DeactivateAnnexCommand;

class DeactivateAnnexCommandTest extends TestCase
{
    /**
     * Test command to deactivate Annex
     *
     * @return void
     */
    public function testDeactivateAnnexCommand()
    {
        Queue::fake();
        $command = new DeactivateAnnexCommand;
        $command->handle();
        Queue::assertPushed(DeactivateAnnex::class);
    }
}
