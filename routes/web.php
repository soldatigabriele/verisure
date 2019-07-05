<?php

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

Route::get('/login', 'VerisureController@login');

Route::get('/logout', 'VerisureController@logout');

Route::get('/status', 'VerisureController@status');

Route::get('/job_status/{jobId}', 'VerisureController@jobStatus');

Route::get('/photo/{deviceId}', 'VerisureController@something');

Route::get('/activate/{systemId}', 'VerisureController@something');

Route::get('/deactivate/{systemId}', 'VerisureController@something');
