<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMagicLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('magic_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token');
            $table->datetime('expiration_date');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::table('magic_logins', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('magic_logins');
    }
}
