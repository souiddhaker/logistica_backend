<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriverIdToNotifsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifs', function (Blueprint $table) {
            $table->unsignedInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifs', function (Blueprint $table) {
            $table->dropForeign('notifs_driver_id_foreign');

            $table->dropColumn('driver_id');
        });
    }
}
