<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->increments('id');

            $table->string('status')->nullable();
            $table->float('total_price')->nullable();
            $table->integer('nbr_luggage')->nullable();
            $table->unsignedInteger('type_car_id')->nullable();
            $table->unsignedInteger('promocode_id')->nullable();
            $table->integer('driver_note')->nullable();

            $table->timestamp('pickup_at')->nullable();
            $table->integer('total_amount')->nullable();
            $table->unsignedInteger('payment_method')->nullable();



            $table->unsignedInteger('client_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->foreign('type_car_id')->references('id')->on('car_categories');
            $table->foreign('promocode_id')->references('id')->on('promocodes');
            $table->foreign('payment_method')->references('id')->on('cards');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
