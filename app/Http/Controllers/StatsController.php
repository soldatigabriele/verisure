<?php

namespace App\Http\Controllers;

use App\Record;
use Carbon\Carbon;
use App\Jobs\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class StatsController extends BaseController
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function get(Request $request)
    {
        $records = Record::orderBy('date', 'desc')
            ->selectRaw("count(id) as count, body, DATE_FORMAT(created_at, '%y-%m-%d') as date")
            ->where('created_at', '>', Carbon::tomorrow()->subDays($request->days ?? 31))
            ->groupBy(DB::raw("body, DATE_FORMAT(created_at, '%y-%m-%d')"))
            ->get();

        $result = [];
        $records->each(function ($record) use (&$result) {
            if (!isset($result[$record->date]['good'])) {
                $result[$record->date]['good'] = 0;
            }
            if (!isset($result[$record->date]['bad'])) {
                $result[$record->date]['bad'] = 0;
            }
            if (in_array($record->body, array_keys(Status::SUCCESS))) {
                $result[$record->date]['good'] = ($result[$record->date]['good'] ?? 0) + $record->count;
            } else {
                $result[$record->date]['bad'] = ($result[$record->date]['bad'] ?? 0) + $record->count;
            }
        });

        foreach ($result as $key => $r) {
            $result[$key]['percentage'] = round(($r['bad'] * 100) / $r['good'], 2);
        }

        return response()->json($result);
    }
}
