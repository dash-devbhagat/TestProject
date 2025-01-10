<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionIdForeignKeyToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->change();  // Ensure it's nullable and matches type
            $table->foreign('transaction_id')
                ->references('id')->on('transactions')
                ->onDelete('set null');  // or 'cascade' depending on your business rules
        });
    }


    public function down()
    {
        // Remove the foreign key if rolling back
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });
    }
}
