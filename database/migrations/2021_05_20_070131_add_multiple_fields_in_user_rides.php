<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleFieldsInUserRides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_rides', function (Blueprint $table) {
            $table->unsignedBigInteger('character_id')->after("ride_route_id")->nullable();
            $table->foreign('character_id')->references('id')->on('characters')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('bike_id')->after("character_id")->nullable();
            $table->foreign('bike_id')->references('id')->on('bikes')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string("cruise_mode")->enum(["true","false"])->after("bike_id")->default("false")->comment("true/false");
            $table->float('remaining_distance')->after('distance')->nullable();
            $table->datetime('start_trip_datetime')->after('remaining_distance')->nullable();
            $table->datetime('end_trip_datetime')->after('start_trip_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_rides', function (Blueprint $table) {
            $table->dropColumn('character_id');
            $table->dropColumn('bike_id');
            $table->dropColumn('cruise_mode');
            $table->dropColumn('remaining_distance');
            $table->dropColumn('start_trip_datetime');
            $table->dropColumn('end_trip_datetime');
        });
    }
}
