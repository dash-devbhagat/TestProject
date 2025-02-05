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
        Deal::create([
            'title' => 'Buy One Get One Free - Red T-Shirt',
            'description' => 'Buy one Red T-Shirt and get one free!',
            'type' => 'BOGO',
            'image' => null,
            'product_id' => 1, // Red T-Shirt
            'product_variant_id' => 2, // Medium size
            'min_quantity' => 1,
            'free_quantity' => 1,
            'free_product_id' => 1, // Free Red T-Shirt
            'free_product_variant_id' => 2, // Same variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => 1
        ]);

        Deal::create([
            'title' => 'Laptop + Red T-Shirt',
            'description' => 'Get a free Red T-Shirt when you buy a Silver Laptop 16GB.',
            'type' => 'Combo',
            'image' => null,
            'product_id' => 3, // Laptop
            'product_variant_id' => 7, // 16GB RAM, 512GB SSD
            'free_product_id' => 1, // Mouse (Assumed ID)
            'free_product_variant_id' => 1, // Mouse variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => 1
        ]);

        Deal::create([
            'title' => '20% Off on Black Jackets',
            'description' => 'Get 20% off on all Black Jackets.',
            'type' => 'Discount',
            'image' => null,
            'product_id' => 2, // Black Jacket
            'product_variant_id' => null, // Applies to all variants
            'percentage' => 20.00,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => 1
        ]);

        Deal::create([
            'title' => 'Buy 2 Get 1 Free - Medium Red T-Shirt',
            'description' => 'Buy two Medium Red T-Shirts and get one free!',
            'type' => 'BOGO',
            'image' => null,
            'product_id' => 1, // Red T-Shirt
            'product_variant_id' => 2, // Medium size
            'min_quantity' => 2,
            'free_quantity' => 1,
            'free_product_id' => 1, // Free Red T-Shirt
            'free_product_variant_id' => 2, // Medium size
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => 1
        ]);

        Deal::create([
            'title' => 'Laptop + Red T-Shirt',
            'description' => 'Buy a Silver Laptop 16GB and get a free Red T-Shirt.',
            'type' => 'Combo',
            'image' => null,
            'product_id' => 3, // Laptop
            'product_variant_id' => 7, // 16GB RAM, 512GB SSD
            'free_product_id' => 1, // Backpack (Assumed ID)
            'free_product_variant_id' => 1, // Backpack variant
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => 1
        ]);
    }
}

