<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create BOGO Deal for Red T-Shirt
        Deal::create([
            'title' => 'Buy 1 Red T-Shirt, Get 1 Free',
            'description' => 'Get a free Red T-Shirt when you buy 1.',
            'type' => 'BOGO',
            'product_id' => 1, // Red T-Shirt
            'product_variant_id' => ProductVarient::where('product_id', 1)->where('unit', 'Medium')->first()->id, // Medium variant
            'min_quantity' => 1,
            'free_quantity' => 1,
            'free_product_id' => 1, // Red T-Shirt
            'free_product_variant_id' => ProductVarient::where('product_id', 1)->where('unit', 'Medium')->first()->id, // Medium variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Create Combo Deal for Black Jacket
        Deal::create([
            'title' => 'Buy 1 Black Jacket + Red T-Shirt for $80',
            'description' => 'Purchase both a Black Jacket and a Red T-Shirt for $80.',
            'type' => 'Combo',
            'product_id' => 2, // Black Jacket
            'product_variant_id' => ProductVarient::where('product_id', 2)->where('unit', 'Medium')->first()->id, // Medium variant
            'min_quantity' => 1,
            'quantity' => 1, // Both items in the combo
            'amount' => 80,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Create Discount Deal for Silver Laptop 16GB
        Deal::create([
            'title' => '10% Off on Silver Laptop 16GB',
            'description' => 'Get a 10% discount on the Silver Laptop with 16GB RAM and 512GB SSD.',
            'type' => 'Discount',
            'product_id' => 3, // Silver Laptop 16GB
            'product_variant_id' => ProductVarient::where('product_id', 3)->first()->id, // Variant for Silver Laptop
            'percentage' => 10, // 10% discount
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Create BOGO Deal for Black Jacket
        Deal::create([
            'title' => 'Buy 1 Black Jacket, Get 1 Free',
            'description' => 'Get a free Black Jacket when you buy 1.',
            'type' => 'BOGO',
            'product_id' => 2, // Black Jacket
            'product_variant_id' => ProductVarient::where('product_id', 2)->where('unit', 'Medium')->first()->id, // Medium variant
            'min_quantity' => 1,
            'free_quantity' => 1,
            'free_product_id' => 2, // Black Jacket
            'free_product_variant_id' => ProductVarient::where('product_id', 2)->where('unit', 'Medium')->first()->id, // Medium variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Create Combo Deal for Red T-Shirt and Silver Laptop
        Deal::create([
            'title' => 'Buy Red T-Shirt and Silver Laptop for $1019',
            'description' => 'Get a Red T-Shirt and Silver Laptop together for $1019.',
            'type' => 'Combo',
            'product_id' => 1, // Red T-Shirt
            'product_variant_id' => ProductVarient::where('product_id', 1)->where('unit', 'Medium')->first()->id, // Medium variant
            'min_quantity' => 1,
            'quantity' => 2, // Both items in the combo
            'amount' => 1019, // Combo price
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // **New Deal 1** - Discount on Red T-Shirt
        Deal::create([
            'title' => '20% Off on Red T-Shirt',
            'description' => 'Get 20% off on all sizes of the Red T-Shirt.',
            'type' => 'Discount',
            'product_id' => 1, // Red T-Shirt
            'product_variant_id' => ProductVarient::where('product_id', 1)->first()->id, // All variants of Red T-Shirt
            'percentage' => 20, // 20% discount
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // **New Deal 2** - Combo Deal for Black Jacket and Laptop
        Deal::create([
            'title' => 'Buy Black Jacket and Silver Laptop for $1050',
            'description' => 'Buy a Black Jacket and Silver Laptop for $1050.',
            'type' => 'Combo',
            'product_id' => 2, // Black Jacket
            'product_variant_id' => ProductVarient::where('product_id', 2)->where('unit', 'Medium')->first()->id, // Medium variant
            'min_quantity' => 1,
            'quantity' => 2, // Both items in the combo
            'amount' => 1050, // Combo price
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // **New Deal 3** - BOGO Deal on Laptop Accessories
        Deal::create([
            'title' => 'Buy 1 Laptop Sleeve, Get 1 Free',
            'description' => 'Buy one Laptop Sleeve, get one free!',
            'type' => 'BOGO',
            'product_id' => 3, // Silver Laptop
            'product_variant_id' => ProductVarient::where('product_id', 3)->first()->id, // Laptop accessory variant
            'min_quantity' => 1,
            'free_quantity' => 1,
            'free_product_id' => 3, // Silver Laptop (assuming accessories are tracked this way)
            'free_product_variant_id' => ProductVarient::where('product_id', 3)->first()->id, // Same variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);
    }
}

