<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->nullable;
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('user_id')->nullable;
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->date('subscription_start_date');
            $table->string('total_price_paid');
            $table->date('subscription_end_date');
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
        Schema::dropIfExists('user_subscriptions');
    }
}
