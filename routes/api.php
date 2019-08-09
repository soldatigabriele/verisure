<?php

Route::middleware(['api', 'token'])->group(function () {
    Route::get('/', function () {
        return app()->version();
    });
    Route::get('/login', 'VerisureController@login');
    Route::get('/logout', 'VerisureController@logout');
    Route::get('/status', 'VerisureController@status');
    /**
     * Examples of routes to activate the alarm
     * 
     * api/activate/house/night
     * api/activate/house/day
     * api/activate/house/house
     * api/activate/garage
     */
    Route::get('/activate/{system}/{mode?}', 'VerisureController@activate');
    Route::get('/deactivate/{system}', 'VerisureController@deactivate');

    /**
     * Note: don't use it , or it will store a Record that could be old
     */
    Route::get('/job_status/{jobId}', function ($jobId) {
        $response = (new \App\VerisureClient)->jobStatus($jobId);
        return response()->json([
            "status" => $response['status'],
            "message" => $response['message'],
        ]);
    });

    Route::get('/records', 'RecordsController@get');
});
