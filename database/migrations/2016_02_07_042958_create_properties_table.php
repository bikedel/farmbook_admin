<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('strSuburb');
            $table->string('numErf');
            $table->string('numPortion');
            $table->string('strStreetNo');
            $table->string('strStreetName');
            $table->string('strSqMeters');
            $table->string('strComplexNo');
            $table->string('strComplexName');
            $table->string('dtmRegDate');
            $table->string('strAmount');
            $table->string('strBondHolder');
            $table->string('strBondAmount');
            $table->string('strOwners');
            $table->string('strIdentity');
            $table->string('strSellers');
            $table->string('strTitleDeed');
            $table->string('strKey');
            $table->timestamps();


            $table->index('strKey');
            $table->index('strIdentity');


  
      

        });




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('properties');
    }
}
