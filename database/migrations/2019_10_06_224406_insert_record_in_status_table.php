<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class InsertRecordInStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('status')->insert([
            [
                'house' => false,
                'garage' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
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
