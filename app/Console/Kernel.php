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

        if (config('verisure.settings.schedule.annex.activate.enabled')) {
            info('annex.activate.enabled command scheduled by Cron');
            $schedule->command('verisure:annex-activate')->cron(config('verisure.settings.schedule.annex.activate.cron'));
        }
        if (config('verisure.settings.schedule.annex.deactivate.enabled')) {
            info('annex.deactivate.enabled command scheduled by Cron');
            $schedule->command('verisure:annex-deactivate')->cron(config('verisure.settings.schedule.annex.deactivate.cron'));
        }
        if (config('verisure.settings.schedule.house.full.enabled')) {
            info('house.full.enabled command scheduled by Cron');
            $schedule->command('verisure:house-activate --mode=house')->cron(config('verisure.settings.schedule.house.full.cron'));
        }
        if (config('verisure.settings.schedule.house.day.enabled')) {
            info('house.day.enabled command scheduled by Cron');
            $schedule->command('verisure:house-activate --mode=day')->cron(config('verisure.settings.schedule.house.day.cron'));
        }
        if (config('verisure.settings.schedule.house.night.enabled')) {
            info('house.night.enabled command scheduled by Cron');
            $schedule->command('verisure:house-activate --mode=night')->cron(config('verisure.settings.schedule.house.night.cron'));
        }
        if (config('verisure.settings.schedule.house.deactivate.enabled')) {
            info('house.deactivate.enabled command scheduled by Cron');
            $schedule->command('verisure:house-deactivate')->cron(config('verisure.settings.schedule.house.deactivate.cron'));
        }
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
