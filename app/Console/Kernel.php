<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ActivateAnnexCommand;
use App\Console\Commands\ActivateHouseCommand;
use App\Console\Commands\RequestStatusCommand;
use App\Console\Commands\DeactivateAnnexCommand;
use App\Console\Commands\DeactivateHouseCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RequestStatusCommand::class,
        ActivateAnnexCommand::class,
        DeactivateAnnexCommand::class,
        ActivateHouseCommand::class,
        DeactivateHouseCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('verisure:status')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
