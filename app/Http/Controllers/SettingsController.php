<?php

namespace App\Http\Controllers;

use App\Session;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SettingsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get list of settings from DB
     *
     * @return void
     */
    public function get()
    {
        return Setting::all();
    }

    /**
     * Update a setting key
     *
     * @return Response
     */
    public function update(Request $request)
    {
        $setting = Setting::where('key', $request->key)->first();
        if (!isset($request->value)) {
            return response()->json([
                "status" => "ko",
                "message" => "value missing",
            ]);
        }

        $setting->update(['value' => $request->value]);
        return response()->json([
            "status" => "ok",
            "message" => $setting->value,
        ]);
    }
}
