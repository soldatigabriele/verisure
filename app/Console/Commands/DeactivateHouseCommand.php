<?php

namespace App\Console\Commands;

use App\Jobs\DeactivateHouse;
use Illuminate\Console\Command;

class DeactivateHouseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verisure:house-deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate the main alarm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        info('house.deactivate.enabled command scheduled by Cron');
        // "false" is the notification
        DeactivateHouse::dispatch(false);
    }
}
