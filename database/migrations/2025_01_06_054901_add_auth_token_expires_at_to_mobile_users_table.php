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
        Schema::table('mobile_users', function (Blueprint $table) {
            $table->timestamp('auth_token_expires_at')->nullable()->after('auth_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            $table->dropColumn('auth_token_expires_at');
        });
    }
};
