<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\VerisureClient;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerisureController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function login()
    {
        try {
            $session = (new VerisureClient)->getSession();
            return response()->json([
                'status' => 'success',
                'session_id' => $session->toArray(),
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getMessage(),
            ], 400);
        }
    }

    public function status()
    {
        $client = new VerisureClient;
        $jobId = $client->status();

        return response()->json([
            "job_id" => $jobId,
        ]);
    }

    public function activate(Request $request)
    {
        if ($request->system == "house") {
            $jobId = (new VerisureClient)->activate($request->mode);
            return response()->json([
                "job_id" => $jobId,
            ]);
        } else if ($request->system == "garage") {
            $jobId = (new VerisureClient)->activateAnnex();
            return response()->json([
                "job_id" => $jobId,
            ]);
        }
        return response()->json([
            "error" => "system not supported",
        ]);
    }

    public function deactivate(Request $request)
    {
        if ($request->system == "house") {
            $jobId = (new VerisureClient)->deactivate();
            return response()->json([
                "job_id" => $jobId,
            ]);
        } else if ($request->system == "garage") {
            $jobId = (new VerisureClient)->deactivateAnnex();
            return response()->json([
                "job_id" => $jobId,
            ]);
        }
        return response()->json([
            "error" => "system not supported",
        ]);
    }

    public function jobStatus()
    {
        info("job status");
        info(Carbon::now());
        \Illuminate\Support\Facades\Cache::increment('requests');
        if (\Illuminate\Support\Facades\Cache::get('requests') > 5) {
            return response()->json([
                'status' => 'activated',
            ]);
        }
        abort(501);
    }

    public function logout()
    {
        try {
            $client = new VerisureClient;
            $client->logout();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getMessage(),
            ], 400);
        }
        return response()->json([
            'status' => 'success',
        ], 200);
    }

}
