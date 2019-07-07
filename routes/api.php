<?php

Route::middleware('auth.token')->group(function () {
    Route::get('/', function () {
        return app()->version();
    });
    Route::get('/login', 'VerisureController@login');
    Route::get('/logout', 'VerisureController@logout');
    Route::get('/status', 'VerisureController@status');
    Route::get('/job_status/{jobId}', 'VerisureController@jobStatus');
    Route::get('/photo/{deviceId}', 'VerisureController@something');
    Route::get('/activate/{systemId}', 'VerisureController@something');
    Route::get('/deactivate/{systemId}', 'VerisureController@something');
});
