<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class InsertDefaultSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings = [
            [
                'key' => 'auth.active',
                'value' => true,
            ],
            [
                'key' => 'auth.token',
                'value' => Str::random(32),
            ],
            [
                'key' => 'notifications.enabled',
                'value' => true,
            ],
            [
                'key' => 'notifications.channel',
                'value' => 'channel',
            ],
            [
                'key' => 'status_job.max_calls',
                'value' => 5,
            ],
            [
                'key' => 'status_job.sleep_between_calls',
                'value' => 3,
            ],
            [
                'key' => 'session.keep_alive',
                'value' => false,
            ],
            [
                'key' => 'session.ttl',
                'value' => 240,
            ],
            [
                'key' => 'censure_responses',
                'value' => true,
            ],
            [
                'key' => 'censure_responses',
                'value' => true,
            ],
            [
                'key' => 'schedule.house.full.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.house.full.cron',
                'value' => '0 0 * * *',
            ],
            [
                'key' => 'schedule.house.day.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.house.day.cron',
                'value' => '0 0 * * *',
            ],
            [
                'key' => 'schedule.house.night.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.house.night.cron',
                'value' => '0 0 * * *',
            ],
            [
                'key' => 'schedule.house.deactivate.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.house.deactivate.cron',
                'value' => '0 0 * * *',
            ],
            [
                'key' => 'schedule.annex.activate.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.annex.activate.cron',
                'value' => '0 0 * * *',
            ],
            [
                'key' => 'schedule.annex.deactivate.enabled',
                'value' => false,
            ],
            [
                'key' => 'schedule.annex.deactivate.cron',
                'value' => '0 0 * * *',
            ],
        ];
        DB::table('settings')->insert($settings);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
