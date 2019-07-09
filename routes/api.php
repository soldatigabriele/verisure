<?php

Route::middleware('auth.token')->group(function () {
    Route::get('/', function () {
        return app()->version();
    });
    Route::get('/login', 'VerisureController@login');
    Route::get('/logout', 'VerisureController@logout');
    Route::get('/status', 'VerisureController@status');
    Route::get('/job_status/{jobId}', 'VerisureController@jobStatus');
    Route::get('/activate/{system}/{mode?}', 'VerisureController@activate');
    Route::get('/deactivate/{system}', 'VerisureController@deactivate');
});
