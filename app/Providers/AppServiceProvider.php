<?php

namespace App\Providers;

use GuzzleHttp\Client;
use App\VerisureClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // app()->bind("verisure.client", function(){
        //     $guzzleClient = new Client(['cookies' => true]);
        //     $client = new VerisureClient($guzzleClient);
        //     // The login method will check if we need to do a login or not
        //     $client->login();
        //     return $client;
        // });
    }
}
