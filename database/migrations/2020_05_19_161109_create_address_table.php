<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->increments('id');
            $table->string('primaryName')->nullable();
            $table->string('secondaryName')->nullable();
            $table->string('place_id')->nullable();
            $table->string('type')->nullable();

            $table->double('longitude')->nullable();
            $table->double('lattitude')->nullable();
            $table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });

        Schema::create('address_trip', function (Blueprint $table) {
            $table->integer('address_id')->unsigned();
            $table->foreign('address_id')->references('id')->on('address')->onDelete('cascade');

            $table->integer('trip_id')->unsigned();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');

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
        Schema::dropIfExists('address_trip');

        Schema::table('address', function(Blueprint $table){

            $table->dropForeign('address_user_id_foreign');
            $table->dropColumn('user_id');

        });
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('address');
    }
}
