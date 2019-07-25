<?php

namespace App\Console\Commands;

use App\VerisureClient;
use App\Events\StatusCreated;
use Illuminate\Console\Command;

class Status extends Command
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
     * Intsance of VerisureClient
     *
     * @var VerisureClient
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VerisureClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $jobId = $this->client->status();
        event(new StatusCreated($jobId));
    }
}
