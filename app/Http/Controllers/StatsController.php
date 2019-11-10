<?php

namespace App\Http\Controllers;

use App\Record;
use App\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class StatsController extends BaseController
{

    /**
     * Undocumented function
     *
     * @return void
     */
    public function get(Request $request)
    {
        $records = Record::orderBy('date', 'desc')
            ->selectRaw("count(id) as count, body, DATE_FORMAT(created_at, '%y-%m-%d') as date")
            ->where('created_at', '>', Carbon::tomorrow()->subDays($request->days ?? 31))
            ->groupBy(\DB::raw("body, DATE_FORMAT(created_at, '%y-%m-%d')"))
            ->get();

        $result = [];
        $records->each(function ($record) use (&$result) {
            if (!isset($result[$record->date]['good'])) {
                $result[$record->date]['good'] = 0;
            }
            if (!isset($result[$record->date]['bad'])) {
                $result[$record->date]['bad'] = 0;
            }
            if (in_array($record->body, [
                'Your Alarm is activated in NIGHT PARTIAL mode and the SECONDARY alarm',
                'Your Alarm has been deactivated',
                'Your Secondary Alarm has been deactivated',
                'Your Alarm is deactivated',
                'Your Secondary Alarm is activated',
                'Your Secondary Alarm has been activated',
                'Your Alarm is activated and your Secondary Alarm',
                'All of your Alarm\'s devices have been activated',
                'Your Alarm has been activated in DAY PARTIAL mode',
                'Your Alarm is activated',
                'Your Alarm is activated in DAY PARTIAL mode and your SECONDARY Alarm',
                'Unable to connect the Alarm. One zone is open, check your windows and/or doors and try again.'])) {
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
