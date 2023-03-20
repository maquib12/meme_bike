<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeatherTypeRideRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('ride_routes', function (Blueprint $table) {           
            $table->unsignedBigInteger('weather_type_id')->after("environment_type_id")->nullable();
            $table->foreign('weather_type_id')->references('id')->on('weathers')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_routes', function (Blueprint $table) {
            
            $table->dropColumn('weather_type_id');
        });
    }
}
