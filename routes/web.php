<?php

use App\User;
use App\Response;
use Carbon\Carbon;
use App\MagicLogin;
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

    Route::get('/stats', 'StatsController@get');

    // Show the latest record
    Route::get('/records', 'RecordsController@get');

    // Request the status
    Route::get('/status', 'VerisureController@status');

    Route::post('/activate/{system}/{mode?}', 'VerisureController@activate');
    Route::post('/deactivate/{system}', 'VerisureController@deactivate');

    Route::get('/options', function () {
        return view('settings');
    })->name('settings');

    /**
     * Magic login routes
     */
    Route::get('/magic-logins', function (Request $request) {
        return view('magic-logins');
    })->name('magic-logins');

    /**
     * Magic Tokens routes for Vue component
     */
    Route::get('/users', function () {
        return User::all();
    });
    Route::get('/magic-logins/all', function () {
        return MagicLogin::where('expiration_date', '>', now()->subHours(48))->with('user')->limit(10)->get();
    });

    Route::post('/magic-logins', function (Request $request) {
        $user = User::findOrFail($request->user_id);
        $ml = new MagicLogin;
        $ml->token = substr(md5(now()), 0, 8);
        $ml->user()->associate($user);
        $ml->expiration_date = now()->addMinutes($request->duration);
        $ml->save();

        return response()->json(['status' => 'ok', 'message' => 'token created', 'token' => $ml]);
    });

    Route::delete('/magic-logins/{id}', function (Request $request) {
        MagicLogin::find($request->id)->delete();
        return response()->json(['status' => 'ok', 'message' => 'token deleted']);
    });

});

Route::get('/ml/{token}', function (Request $request) {
    $user = MagicLogin::where('expiration_date', '>', Carbon::now())->where('token', $request->token)->firstOrFail()->user;
    Auth::login($user);
    return redirect()->route('home');
})->name('magic-login');
