<?php

namespace Database\Seeders;

use App\Models\ProductVarient; // Ensure this model exists in the specified namespace
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        ProductVarient::create([
            'product_id' => 1, // Product ID for Red T-Shirt
            'unit' => 'Small',
            'price' => 19,
        ]);

        ProductVarient::create([
            'product_id' => 1, // Product ID for Red T-Shirt
            'unit' => 'Medium',
            'price' => 22,
        ]);

        ProductVarient::create([
            'product_id' => 1, // Product ID for Red T-Shirt
            'unit' => 'Large',
            'price' => 25,
        ]);

        // Create variants for the "Black Jacket"
        ProductVarient::create([
            'product_id' => 2, // Product ID for Black Jacket
            'unit' => 'Small',
            'price' => 60,
        ]);

        ProductVarient::create([
            'product_id' => 2, // Product ID for Black Jacket
            'unit' => 'Medium',
            'price' => 70,
        ]);

        ProductVarient::create([
            'product_id' => 2, // Product ID for Black Jacket
            'unit' => 'Large',
            'price' => 80,
        ]);

        // Create variants for the "Silver Laptop 16GB"
        ProductVarient::create([
            'product_id' => 3, // Product ID for Silver Laptop 16GB
            'unit' => '16GB RAM, 512GB SSD',
            'price' => 1000,
        ]);
    }
}
