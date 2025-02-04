<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['BOGO', 'Combo', 'Discount']);
            $table->string('image')->nullable(); // Image for the deal
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->integer('min_quantity')->nullable(); // Minimum quantity required for the deal
            $table->integer('free_quantity')->nullable(); // Free quantity (for BOGO)
            $table->unsignedBigInteger('free_product_id')->nullable(); // Free product (if applicable)
            $table->unsignedBigInteger('free_product_variant_id')->nullable();
            $table->integer('quantity')->nullable(); // Quantity for Combo deals
            $table->decimal('amount', 10, 2)->nullable(); // Discounted amount (for Discount deals)
            $table->decimal('percentage', 5, 2)->nullable(); // Discount percentage (for Discount deals)
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_varients')->onDelete('cascade');
            $table->foreign('free_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('free_product_variant_id')->references('id')->on('product_varients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
};
