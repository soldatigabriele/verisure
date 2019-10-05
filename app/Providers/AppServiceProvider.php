<?php

namespace App\Providers;

use App\VerisureClient;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;

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

        if(!is_test() && Schema::hasTable('settings')){
            // Load the custom configuration from the DB
            config(['verisure.settings' => load_custom_config()]);
        }

        // Add macro to paginate a collection
        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $pageName = 'page', $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage($pageName) ?: 1);
                $options['pageName'] = $pageName;
                return (new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, $options))->withPath('');
            });
        }

    }
}
