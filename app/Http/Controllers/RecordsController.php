<?php

namespace App\Http\Controllers;

use App\Status;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecordsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the latest status recorded of the alarm
     *
     * Garage:
     * OFF    0
     * ON     1
     *
     * House:
     * OFF    0
     * FULL   1
     * DAY    2
     * NIGHT  3
     *
     * @return void
     */
    public function get(Request $request)
    {
        $status = Status::firstOrFail();
        $map = [
            0 => 'OFF',
            1 => 'ON',
            2 => 'DAY',
            3 => 'NIGHT',
        ];
        $response = [
            "house" => $status->house,
            "garage" => $status->garage,
            "age" => $status->updated_at->timestamp,
        ];

        // Format the response for humans
        if ($request->format == 'for_humans') {
            $response = [
                "message" => "House: " . $map[$status->house] . " Garage: " . $map[$status->garage],
                "age" => $status->updated_at->diffForHumans(),
            ];
        }
        return response()->json($response);
    }
}
