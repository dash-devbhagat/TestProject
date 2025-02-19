<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->enum('type', ['BOGO', 'Combo', 'Discount', 'Flat'])->default('BOGO'); // Deal type
            $table->string('title'); // Deal title
            $table->text('description'); // Deal description
            $table->string('image')->nullable(); // Deal image
            $table->date('start_date'); // Start date of the deal
            $table->date('end_date'); // End date of the deal
            $table->string('renewal_time'); // Renewal time (e.g., 1 hour, 1 week)
            $table->boolean('is_active')->default(true); // Deal status

            // BOGO and Flat deal fields
            $table->unsignedBigInteger('buy_product_id')->nullable(); // Buy product ID
            $table->unsignedBigInteger('buy_variant_id')->nullable(); // Buy product variant ID
            $table->integer('buy_quantity')->nullable(); // Buy product quantity
            $table->unsignedBigInteger('get_product_id')->nullable(); // Get product ID
            $table->unsignedBigInteger('get_variant_id')->nullable(); // Get product variant ID
            $table->integer('get_quantity')->nullable(); // Get product quantity

            // Combo deal fields
            $table->decimal('combo_discounted_amount', 10, 2)->nullable(); // Combo discounted amount

            // Discount deal fields
            $table->decimal('min_cart_amount', 10, 2)->nullable(); // Minimum cart amount for discount
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable(); // Discount type
            $table->decimal('discount_amount', 10, 2)->nullable(); // Discount amount

            // Timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('buy_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('buy_variant_id')->references('id')->on('product_varients')->onDelete('cascade');
            $table->foreign('get_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('get_variant_id')->references('id')->on('product_varients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
};
