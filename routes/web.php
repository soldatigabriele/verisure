<?php

use App\User;
use App\Setting;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Auth::routes(['register' => false, 'reset' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $responses = \App\Response::latest('id')->get();
        return view('home')->with(['responses' => $responses]);
    })->name('home');

    Route::get('/requests', function () {
        $requests = \App\Request::latest('id')->paginate(20);
        return view('requests')->with(['requests' => $requests]);
    })->name('requests');

    Route::get('/request/{request}', function (\App\Request $request) {
        return view('request')->with(['request' => $request]);
    })->name('request');

    Route::get('/responses', function (Request $request) {
        $responses = \App\Response::latest('id')->get();
        if ($request->has('excluded_statuses')) {
            $statuses = explode(',', $request->excluded_statuses);
            $responses = $responses->filter(function ($response) use ($statuses) {
                if (isset($response->body['status'])) {
                    return (!in_array($response->body['status'], $statuses));
                }
            });
        }
        return view('responses');
    })->name('responses');

    Route::get('/response/{response}', function (Response $response) {
        return view('response')->with(['response' => $response]);
    })->name('response');

    // Get the list of settings
    Route::get('/settings', 'SettingsController@get');
    // Update a specific setting
    Route::post('/settings', 'SettingsController@update');

    // Delete the sessions
    Route::delete('/sessions', function () {
        \App\Session::query()->delete();
        return response()->json([
            "status" => "ok",
            "message" => "All sessions have been invalidate",
        ]);
    });

    // Show the latest record
    Route::get('/records', 'RecordsController@get');
    
    // Request the status
    Route::get('/status', 'VerisureController@status');

    Route::post('/activate/{system}/{mode?}', 'VerisureController@activate');
    Route::post('/deactivate/{system}', 'VerisureController@deactivate');

    Route::get('/options', function () {
        return view('settings');
    })->name('settings');
});

Route::get('/magic-login', function (Request $request) {
    if (isset($request->auth_token) && $request->auth_token == Setting::where('key', 'auth.token')->first()->value) {
        Auth::login(User::first(), true);
    }
    return redirect()->route('home');
})->name('magic-login');
