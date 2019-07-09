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
        app()->bind(VerisureClient::class, function () {
            return new VerisureClient;
        });
    }
}
