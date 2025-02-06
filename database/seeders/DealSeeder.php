<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Product;
use App\Models\ProductVarient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // BOGO Deal: Buy 1 Red T-Shirt (Medium) and Get 1 Free
        Deal::create([
            'type' => 'BOGO',
            'title' => 'Buy 1 Red T-Shirt (Medium), Get 1 Free',
            'description' => 'Buy 1 Red T-Shirt in Medium size, Get 1 Free of the same product.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(7),
            'renewal_time' => '1',
            'is_active' => true,
            'buy_product_id' => 1, // Red T-Shirt
            'buy_variant_id' => 2, // Medium variant
            'buy_quantity' => 1,
            'get_product_id' => 1, // Red T-Shirt
            'get_variant_id' => 2, // Medium variant
            'get_quantity' => 1,
            'actual_amount' => 22.00, // Price of 1 Red T-Shirt Medium
        ]);

        // Combo Deal: 1 Red T-Shirt + 1 Black Jacket for 70rs
        Deal::create([
            'type' => 'Combo',
            'title' => 'Red T-Shirt & Black Jacket',
            'description' => 'Get 1 Red T-Shirt and 1 Black Jacket for only 70rs.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(10),
            'renewal_time' => '1',
            'is_active' => true,
            'actual_amount' => 79.00,
            'combo_discounted_amount' => 70.00,
        ]);

        // Discount Deal: 10% off on orders above 1000rs
        Deal::create([
            'type' => 'Discount',
            'title' => '10% Discount on Orders Above 1000rs',
            'description' => 'Get a 10% discount on cart total if the total exceeds 1000rs.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(14),
            'renewal_time' => '2',
            'is_active' => true,
            'min_cart_amount' => 1000.00,
            'discount_type' => 'percentage',
            'discount_amount' => 10.00,
        ]);

        // Flat Deal: Flat 100rs off on Red T-Shirt (Medium)
        Deal::create([
            'type' => 'Flat',
            'title' => 'Flat 100rs Off on Red T-Shirt (Medium)',
            'description' => 'Get a flat 100rs off on Red T-Shirt in Medium size.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(7),
            'renewal_time' => '7',
            'is_active' => true,
            'buy_product_id' => 1, // Red T-Shirt
            'buy_variant_id' => 2, // Medium variant
            'buy_quantity' => 1,
            'actual_amount' => 25.00,
            'discount_type' => 'fixed',
            'discount_amount' => 100.00,
        ]);

        // BOGO Deal: Buy 2 Black Jackets and Get 1 Free
        Deal::create([
            'type' => 'BOGO',
            'title' => 'Buy 2 Black Jackets, Get 1 Free',
            'description' => 'Buy 2 Black Jackets (Medium), Get 1 Free.',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(14),
            'renewal_time' => '30',
            'is_active' => true,
            'buy_product_id' => 2, // Black Jacket
            'buy_variant_id' => 2, // Medium variant
            'buy_quantity' => 2,
            'get_product_id' => 2, // Black Jacket
            'get_variant_id' => 2, // Medium variant
            'get_quantity' => 1,
            'actual_amount' => 70.00,
        ]);
    }
}

