<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPromocodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocode_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('promocode_id')->unsigned();
            $table->foreign('promocode_id')->references('id')->on('promocodes')->onDelete('cascade');

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
        Schema::table('promocode_user', function(Blueprint $table){

            $table->dropForeign('promocode_user_user_id_foreign');
            $table->dropForeign('promocode_user_promocode_id_foreign');

            $table->dropColumn('user_id');
            $table->dropColumn('promocode_id');

        });
        Schema::dropIfExists('promocode_user');
    }
}
