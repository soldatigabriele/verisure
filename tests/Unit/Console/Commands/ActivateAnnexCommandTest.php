<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use App\Jobs\ActivateAnnex;
use Illuminate\Support\Facades\Queue;
use App\Console\Commands\ActivateAnnexCommand;

class ActivateAnnexCommandTest extends TestCase
{
    /**
     * Test command to activate Annex
     *
     * @return void
     */
    public function testActivateAnnexCommand()
    {
        Queue::fake();
        $command = new ActivateAnnexCommand;
        $command->handle();
        Queue::assertPushed(ActivateAnnex::class);
    }
}
