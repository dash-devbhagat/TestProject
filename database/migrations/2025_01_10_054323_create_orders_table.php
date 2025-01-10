<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'in progress', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('charges_total', 10, 2)->nullable();
            $table->decimal('grand_total', 10, 2);
            $table->unsignedBigInteger('address_id');
            $table->string('transaction_id')->nullable();
            $table->enum('transaction_status', ['failed', 'pending', 'success'])->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('mobile_users')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
