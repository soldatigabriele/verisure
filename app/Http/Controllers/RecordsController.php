<?php

namespace App\Http\Controllers;

use App\Record;
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
     * @return void
     */
    public function get()
    {
        $record = Record::latest('id')->first();

        if ($record) {

            return response()->json([
                "message" => $record->body,
                "age" => $record->created_at->diffForHumans(),
            ]);
        }
        return response()->json([
            "message" => "no record found",
            "age" => now(),
        ]);
    }
}
