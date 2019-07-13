<?php

Route::middleware('auth.token')->group(function () {
    Route::get('/', function () {
        return app()->version();
    });
    Route::get('/login', 'VerisureController@login');
    Route::get('/logout', 'VerisureController@logout');
    Route::get('/status', 'VerisureController@status');
    Route::get('/activate/{system}/{mode?}', 'VerisureController@activate');
    Route::get('/deactivate/{system}', 'VerisureController@deactivate');
    // Route::get('/job_status/{jobId}', function($jobId){
        // TODO dispatch the job status job
    // });
});
