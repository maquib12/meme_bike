<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderHeightWeightCountryInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->enum(['male', 'female', 'other'])->default("male")->after('profile_pic')->comment("'male', 'female', 'other'")->nullable();
             $table->string('height')->after('gender')->nullable();
             $table->string('weight')->after('height')->nullable();
             $table->string('country')->after('weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('height');
            $table->dropColumn('weight');
            $table->dropColumn('country');
        });
    }
}
