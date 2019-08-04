<?php

namespace App\Console\Commands;

use App\Jobs\ActivateHouse;
use App\Jobs\DeactivateAnnex;
use Illuminate\Console\Command;

class ActivateHouseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verisure:house-activate
        {--mode=: Alarm mode house, night, day}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate the main alarm';

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
        $mode = $this->option('mode') ?? 'house';
        // "false" is the notification
        ActivateHouse::dispatch($mode, false);
    }
}
