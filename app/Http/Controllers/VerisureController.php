<?php

namespace App\Http\Controllers;

use App\Jobs\Login;
use App\Jobs\Activate;
use App\VerisureClient;
use App\Jobs\ActivateAnnex;
use App\Jobs\ActivateHouse;
use App\Jobs\RequestStatus;
use Illuminate\Http\Request;
use App\Jobs\DeactivateAnnex;
use App\Jobs\DeactivateHouse;
use Illuminate\Routing\Controller as BaseController;

class VerisureController extends BaseController
{
    /**
     * VerisureClient interface
     *
     * @var VerisureClient
     */
    protected $client;

    /**
     * Controller constructor
     *
     * @param VerisureClient $client
     */
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
        Login::dispatch();
        return response()->json(['status' => 'accepted'], 202);
    }

    /**
     * Get the status of the alarm
     *
     * @return void
     */
    public function status()
    {
        RequestStatus::dispatch(true);
        return response()->json(["stauts" => "accepted"], 202);
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
        $delay = $request->has('delay') ? $request->delay : 0;
        if ($request->system == "house") {
            $mode = in_array($request->mode, ['house', 'night', 'day']) ? $request->mode : 'house';
            ActivateHouse::dispatch($mode)->delay(now()->addMinutes($delay));
        } else {
            // System is garage
            ActivateAnnex::dispatch()->delay(now()->addMinutes($delay));
        }
        return response()->json(['status' => 'accepted'], 202);
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
        $delay = $request->has('delay') ? $request->delay : 0;
        if ($request->system == "house") {
            DeactivateHouse::dispatch()->delay(now()->addMinutes($delay));
        } else if ($request->system == "garage") {
            DeactivateAnnex::dispatch()->delay(now()->addMinutes($delay));
        }
        return response()->json(['status' => 'accepted'], 202);
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
        return response()->json(['status' => 'success'], 200);
    }
}
