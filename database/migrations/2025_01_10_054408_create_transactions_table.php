<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('payment_mode', ['online', 'cash on delivery']);
            $table->enum('payment_type', ['credit', 'debit', 'upi'])->nullable();
            $table->enum('payment_status', ['failed', 'pending', 'success'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('mobile_users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
