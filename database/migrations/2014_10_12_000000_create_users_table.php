<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable(); 
            $table->string('storename')->nullable(); 
            $table->string('location')->nullable(); 
            $table->decimal('latitude', 10, 8)->nullable();  // (-90.00000000 to 90.00000000)
            $table->decimal('longitude', 11, 8)->nullable(); // (-180.00000000 to 180.00000000)
            $table->string('logo')->nullable();
            $table->boolean('isProfile')->default(false); //Flag to check if the user profile is colmpleted or not
            $table->boolean('isDelete')->default(false); 
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
