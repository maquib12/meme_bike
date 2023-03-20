<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_id')->nullable;
            $table->foreign('advertisement_id')->references('id')->on('advertisements')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisement_locations');
    }
}
