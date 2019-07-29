<?php

namespace App\Console\Commands;

use App\Jobs\RequestStatus;
use Illuminate\Console\Command;

class RequestStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verisure:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get status of the alarm';

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
        if (config('verisure.session.keep_alive')) {
            // "false" is the notification
            RequestStatus::dispatch(false)->onQueue('high');
        }
    }
}
