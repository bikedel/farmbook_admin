<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('strIDNumber');
            $table->string('TITLE');
            $table->string('INITIALS');
            $table->string('NAME');
            $table->string('strSurname');
            $table->string('strFirstName');
            $table->string('strHomePhoneNo');
            $table->string('strWorkPhoneNo');
            $table->string('strCellPhoneNo');
            $table->string('EMAIL');
            $table->timestamps();

            $table->index('strIDNumber');
                  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('owners');
    }
}
