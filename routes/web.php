<?php

use App\Request;
use App\Response;

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

    Route::get('/request/{request}', function (Request $request) {
        return view('request')->with(['request' => $request]);
    })->name('request');

    Route::get('/responses', function () {
        $responses = \App\Response::latest('id')->paginate(20);
        return view('responses')->with(['responses' => $responses]);
    })->name('responses');

    Route::get('/response/{response}', function (Response $response) {
        return view('response')->with(['response' => $response]);
    })->name('response');

    // Get the list of settings
    Route::get('/settings', 'SettingsController@get');
    // Update a specific setting
    Route::post('/settings', 'SettingsController@update');

    // Delete the sessions
    Route::delete('/sessions', function(){
        \App\Session::query()->delete();
        return response()->json([
            "status" => "ok",
            "message" => "All sessions have been invalidate",
        ]);
    });
    
    Route::get('/options', function(){
        return view('settings');
    })->name('settings');
});