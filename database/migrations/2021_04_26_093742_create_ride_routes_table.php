<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRideRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ride_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('environment_type_id')->nullable;
            $table->foreign('environment_type_id')->references('id')->on('environment_types')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('start_from_location');
            $table->string('start_from_lat');
            $table->string('start_from_lng');
            $table->string('end_from_location');
            $table->string('end_from_lat');
            $table->string('end_from_lng');
            $table->tinyInteger('status')->comment("active=1,inactive=0");
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
        Schema::dropIfExists('ride_routes');
    }
}
