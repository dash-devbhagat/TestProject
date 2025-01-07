<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id');
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->text('address_line');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('state_id');
            $table->string('zip_code');
            $table->unsignedBigInteger('user_id'); // Add user_id to link with the mobile_users table
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('mobile_users')->onDelete('cascade'); // Foreign key to mobile_users
        });

        Schema::table('mobile_users', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable()->after('referral_code');

            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });

        Schema::dropIfExists('addresses');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
    }
};
