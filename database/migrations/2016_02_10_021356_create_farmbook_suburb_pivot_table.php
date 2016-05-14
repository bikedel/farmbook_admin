<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFarmbookSuburbPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmbook_suburb', function (Blueprint $table) {
            $table->integer('farmbook_id')->unsigned()->index();
            $table->foreign('farmbook_id')->references('id')->on('farmbooks')->onDelete('cascade');
            $table->integer('suburb_id')->unsigned()->index();
            $table->foreign('suburb_id')->references('id')->on('suburbs')->onDelete('cascade');
            $table->primary(['farmbook_id', 'suburb_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('farmbook_suburb');
    }
}
