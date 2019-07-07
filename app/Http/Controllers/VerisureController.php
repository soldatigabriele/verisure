<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\VerisureClient;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerisureController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Login
     *
     * @return void
     */
    public function login()
    {
        try {
            new VerisureClient;
        } catch (\Throwable $th) {
            return response()->json(['status' => $th->getMessage()], 400);
        }
        return response()->json(['status' => 'success'], 200);
    }

    public function status()
    {
        info(Carbon::now());
        return response()->json(["job_id" => "1234657"]);

        $client = new VerisureClient;
        $jobId = $client->status();

    }

    public function jobStatus()
    {
        info("job status");
        info(Carbon::now());
        \Illuminate\Support\Facades\Cache::increment('requests');
        if (\Illuminate\Support\Facades\Cache::get('requests') > 5) {
            return response()->json(['status' => 'activated']);
        }
        abort(501);
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        try {
            $client = new VerisureClient;
            $client->logout();
        } catch (\Throwable $th) {
            return response()->json(['status' => $th->getMessage()], 400);
        }
        return response()->json(['status' => 'success'], 200);
    }

}
