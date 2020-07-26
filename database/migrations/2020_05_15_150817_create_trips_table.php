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
            $table->text('driver_note')->nullable();
            $table->text('route')->nullable();

            $table->timestamp('pickup_at')->nullable();
            $table->unsignedInteger('payment_method')->nullable();

            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('type_car_id')->references('id')->on('car_categories')->onDelete('restrict');
            $table->foreign('promocode_id')->references('id')->on('promocodes')->onDelete('restrict');
            $table->foreign('payment_method')->references('id')->on('cards')->onDelete('restrict');

            $table->integer('transaction_status')->nullable();
            $table->text('transaction_note')->nullable();
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
        Schema::table('trips', function(Blueprint $table){

            $table->dropForeign('trips_user_id_foreign');
            $table->dropForeign('trips_driver_id_foreign');
            $table->dropForeign('trips_type_car_id_foreign');
            $table->dropForeign('trips_promocode_id_foreign');
            $table->dropForeign('trips_payment_method_foreign');

            $table->dropColumn('user_id');
            $table->dropColumn('driver_id');
            $table->dropColumn('type_car_id');
            $table->dropColumn('promocode_id');
            $table->dropColumn('payment_method');

        });
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('trips');

    }
}
