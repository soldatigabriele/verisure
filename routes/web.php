<?php

use App\Request;
use App\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('', function() {
    return redirect("api/");
});


Auth::routes();

Route::get('/home', function(){
    return view('home');
})->name('home');

Route::get('/monitor', function(){
    return view('monitor');
})->name('monitor');

Route::get('/requests', function(){
    $requests = \App\Request::latest('id')->limit(40)->get();
    return view('requests')->with(['requests' => $requests]);
})->name('requests');

Route::get('/request/{request}', function (Request $request) {
    return view('request')->with(['request' => $request]);
})->name('request');

Route::get('/responses', function(){
    $responses = \App\Response::latest('id')->limit(40)->get();
    return view('responses')->with(['responses' => $responses]);
})->name('responses');

Route::get('/response/{response}', function(Response $response){
    return view('response')->with(['response' => $response]);
})->name('response');

// Route::get('', function() {
//     return redirect("api/");
// });
