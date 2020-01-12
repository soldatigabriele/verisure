<?php

use App\Response;
use Illuminate\Http\Request;

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

Route::get('/responses', function (Request $request) {
    $responses = collect();
    $query = \App\Response::latest('id');
    // The view expects all the responses. If we have no new responses, return an empty collection
    if (!$request->has('latest_id') || \App\Response::where('id', '>', $request->latest_id)->count() !== 0){
        // TODO we need to pass a parameter to change the limit of requests
        $responses = $query->limit($request->limit ?? 1000)->get();
        if ($request->has('excluded_statuses')) {
            $statuses = explode(',', $request->excluded_statuses);
            $responses = $responses->filter(function ($response) use ($statuses) {
                if (in_array('jobs', $statuses) && isset($response->body['job_id'])) {
                    return false;
                }
                if (isset($response->body['status'])) {
                    return (!in_array($response->body['status'], $statuses));
                } else {
                    return !in_array($response->request_type, $statuses);
                }
                return true;
            });
        }
    }
    // Reset the collection keys
    $responses = $responses->values();
    return response()->json($responses->paginate($request->per_page ?? 13));
})->name('responses');

Route::get('/response/{response}', function (Response $response) {
    return view('response')->with(['response' => $response]);
})->name('response');
