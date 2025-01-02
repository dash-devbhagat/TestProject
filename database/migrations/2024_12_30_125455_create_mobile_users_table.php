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
        Schema::create('mobile_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('profilepic')->nullable();
            $table->date('birthdate')->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->boolean('is_profile_complete')->default(0);
            $table->string('auth_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('device_type')->nullable();
            $table->string('referral_code')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamps();

            $table->foreign('referred_by')->references('id')->on('mobile_users')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('mobile_users');
    }
};
