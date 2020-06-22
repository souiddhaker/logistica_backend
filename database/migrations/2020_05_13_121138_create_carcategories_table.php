<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model')->nullable();
            $table->float('price')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->string('range_luggage')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->integer('price_100')->nullable();
            $table->integer('price_101')->nullable();
            $table->integer('price_1')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('car_categories');
    }
}
