<?php

namespace App\Http\Controllers;

use App\Jobs\Login;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateAnnex;
use App\Jobs\ActivateHouse;
use Illuminate\Http\Request;
use App\Events\StatusCreated;
use App\Jobs\DeactivateAnnex;
use App\Jobs\DeactivateHouse;
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

    /**
     * Log in the Verisure app
     *
     * @return void
     */
    public function login()
    {
        // Dispatch the job
        Login::dispatch();

        return response()->json([
            'status' => 'accepted',
        ], 202);
    }

    /**
     * Get the status of the alarm
     *
     * @return void
     */
    public function status()
    {
        $jobId = $this->client->status();
        event(new StatusCreated($jobId));

        return response()->json([
            "job_id" => $jobId,
        ]);
    }

    /**
     * Request the activation of a system
     *
     * @param Request $request
     * @return void
     */
    public function activate(Request $request)
    {
        if (!in_array($request->system, ["house", "garage"])) {
            return response()->json([
                'error' => 'system not supported, try with "/house" or "/garage"',
            ], 400);
        }

        if ($request->system == "house") {
            ActivateHouse::dispatch($request->mode);
        } else {
            // System is garage
            ActivateAnnex::dispatch($request->mode);
        }
        return response()->json([
            'status' => 'accepted',
        ], 202);
    }

    /**
     * Request the deactivation of a system
     *
     * @param Request $request
     * @return void
     */
    public function deactivate(Request $request)
    {
        if (!in_array($request->system, ["house", "garage"])) {
            return response()->json([
                'error' => 'system not supported, try with "/house" or "/garage"',
            ], 400);
        }
        if ($request->system == "house") {
            DeactivateHouse::dispatch();
        } else if ($request->system == "garage") {
            DeactivateAnnex::dispatch();
        }
        return response()->json([
            'status' => 'accepted',
        ], 202);
    }

    /**
     * Logout from Verisure
     *
     * @return void
     */
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
