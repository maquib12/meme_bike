<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->tinyInteger('status')->comment("active=1,inactive=0");
            $table->unsignedBigInteger('user_type_id');
            $table->foreign('user_type_id')->references('id')->on('table_user_types')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth');
            $table->string('otp')->nullable();
            $table->boolean('is_otp_verified')->default('1')->comment('0=Not Verified,1=Verified');
            $table->string('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('profile_pic')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
