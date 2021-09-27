<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->unsignedBigInteger('person_id');
            $table->text('value');
            $table->timestamps();

            $table->foreign('field_id')->references('id')->on('persons_unique_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons_data');
    }
}
