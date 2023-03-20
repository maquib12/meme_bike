<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Weathers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('weathers', function (Blueprint $table) {
            $table->id();
            $table->string('weather_type');
            $table->string('picture')->nullable();
            $table->string('picture_on_hover')->nullable();
            $table->datetime('last_update_date');
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
        Schema::dropIfExists('weathers');
    }
}
