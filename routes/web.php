<?php

use App\Request;
use App\Response;

Auth::routes(['register' => false, 'reset' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/', function(){
        $responses = \App\Response::latest('id')->get();
        // dd($responses);
        return view('home')->with(['responses' => $responses]);
    })->name('home');

    Route::get('/requests', function(){
        $requests = \App\Request::latest('id')->paginate(20);
        return view('requests')->with(['requests' => $requests]);
    })->name('requests');
    
    Route::get('/request/{request}', function (Request $request) {
        return view('request')->with(['request' => $request]);
    })->name('request');
    
    Route::get('/responses', function(){
        $responses = \App\Response::latest('id')->paginate(20);
        return view('responses')->with(['responses' => $responses]);
    })->name('responses');
    
    Route::get('/response/{response}', function(Response $response){
        return view('response')->with(['response' => $response]);
    })->name('response');
});
