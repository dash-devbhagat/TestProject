<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('user_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
