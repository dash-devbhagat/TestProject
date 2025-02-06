<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('grand_total');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->decimal('combo_discount', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
            $table->dropColumn('combo_discount');
        });
    }
};
