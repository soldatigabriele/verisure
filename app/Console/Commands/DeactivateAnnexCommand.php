<?php

namespace App\Console\Commands;

use App\Jobs\DeactivateAnnex;
use Illuminate\Console\Command;

class DeactivateAnnexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verisure:annex-deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate the Annex alarm';

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
        // "false" is the notification
        DeactivateAnnex::dispatch(false);
    }
}
