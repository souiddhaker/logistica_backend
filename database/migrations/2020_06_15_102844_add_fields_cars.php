<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsCars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('car_categories', function (Blueprint $table) {
            $table->integer('price_100')->nullable();
            $table->integer('price_101')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        Schema::table('car_categories', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('price_100');
            $table->dropColumn('price_101');
        });
    }
}
