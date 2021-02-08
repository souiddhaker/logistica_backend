<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label')->nullable();
            $table->float('price')->nullable();
            $table->unsignedInteger('category_id')->nullable();

            $table->foreign('category_id')->references('id')->on('categories_services');

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
        Schema::table('services', function(Blueprint $table){

            $table->dropForeign('services_category_id_foreign');

            $table->dropColumn('category_id');

        });
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('services');
    }
}
