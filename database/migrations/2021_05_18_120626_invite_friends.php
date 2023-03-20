<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InviteFriends extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_ride_id')->nullable;
            $table->foreign('user_ride_id')->references('id')->on('user_rides')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('user_id')->nullable;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('friend_user_id')->nullable;
            $table->foreign('friend_user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('CASCADE');
             $table->tinyInteger('status')->comment("invite = 1 , join group = 2 , leave group=3");
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
         Schema::dropIfExists('invite_friends');
    }
}
