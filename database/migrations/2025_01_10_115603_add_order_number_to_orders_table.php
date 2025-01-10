<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderNumberToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add the new order_number column
            $table->string('order_number')->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove the order_number column
            $table->dropColumn('order_number');
        });
    }
}
