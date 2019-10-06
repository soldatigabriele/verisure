<?php

namespace App\Http\Controllers;

use App\Status;
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
    public function get()
    {
        $status = Status::firstOrFail();
        $map = [
            0 => 'OFF',
            1 => 'ON',
            2 => 'DAY',
            3 => 'NIGHT',
        ];
        return response()->json([
            "message" => "House: " . $map[$status->house] . " Garage: " . $map[$status->garage],
            "age" => $status->updated_at->diffForHumans(),
        ]);
    }
}
