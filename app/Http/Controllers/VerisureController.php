<?php

namespace App\Http\Controllers;

use App\VerisureClient;
use Illuminate\Http\Request;
use App\Events\StatusCreated;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerisureController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $client;

    public function __construct(VerisureClient $client)
    {
        $this->client = $client;
    }

    public function login()
    {
        try {
            $this->client->login();
            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getMessage(),
            ], 400);
        }
    }

    public function status()
    {
        $jobId = $this->client->status();
        event(new StatusCreated($jobId));

        return response()->json([
            "job_id" => $jobId,
        ]);
    }

    public function activate(Request $request)
    {
        if (!in_array($request->system, ["house", "garage"])) {
            return response()->json([
                'error' => 'system not supported, try with "/house" or "/garage"',
            ]);
        }

        try {
            if ($request->system == "house") {
                $jobId = $this->client->activate($request->mode);
                event(new StatusCreated($jobId));
                return response()->json([
                    "job_id" => $jobId,
                ]);
            }
            // System is garage
            $jobId = $this->client->activateAnnex();
            event(new StatusCreated($jobId));
            return response()->json([
                "job_id" => $jobId,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getMessage(),
            ], 400);
        }
    }

    public function deactivate(Request $request)
    {
        if ($request->system == "house") {
            $jobId = $this->client->deactivate();
            event(new StatusCreated($jobId));
            return response()->json([
                "job_id" => $jobId,
            ]);
        } else if ($request->system == "garage") {
            $jobId = $this->client->deactivateAnnex();
            event(new StatusCreated($jobId));
            return response()->json([
                "job_id" => $jobId,
            ]);
        }
        return response()->json([
            "error" => "system not supported",
        ]);
    }

    public function logout()
    {
        try {
            $this->client->logout();
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
