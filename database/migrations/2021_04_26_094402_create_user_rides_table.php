<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('ride_route_id')->nullable;
            $table->foreign('ride_route_id')->references('id')->on('ride_routes')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->float('distance');
            $table->float('average_speed');
            $table->float('total_time');
            $table->float('total_calories_burn');
            $table->tinyInteger('solo_or_group_trip')->comment("solo=1,group_trip=0");
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
        Schema::dropIfExists('user_rides');
    }
}
