<?php

namespace App\Console\Commands;

use App\Record;
use App\Request;
use App\Session;
use App\Response;
use Illuminate\Console\Command;

class DbClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean {days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete records older than N days';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = $this->arguments()['days'];

        Record::where('created_at', '<', today()->subDays($days))->delete();

        Session::where('created_at', '<', today()->subDays($days))->forceDelete();

        $requests = Request::where('created_at', '<', today()->subDays($days))->get();
        $requests->each(function ($request) {
            if ($request->response) {
                $request->response->delete();
            }
            $request->delete();
        });

        return 0;
    }
}
