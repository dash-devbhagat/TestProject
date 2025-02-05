<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealComboProductsTable extends Migration
{
    public function up()
    {
        Schema::create('deal_combo_products', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('deal_id'); // Foreign key to deals table
            $table->unsignedBigInteger('product_id'); // Foreign key to products table
            $table->unsignedBigInteger('variant_id'); // Foreign key to product_variants table
            $table->integer('quantity'); // Quantity of the product in the combo
            // Add timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_varients')->onDelete('cascade');

            // Composite primary key (deal_id, product_id, variant_id)
            $table->unique(['deal_id', 'product_id', 'variant_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_combo_products');
    }
}