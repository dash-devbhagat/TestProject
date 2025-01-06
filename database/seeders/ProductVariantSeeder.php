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
            'product_id' => 1, // Product ID for Red T-shirt
            'unit' => 'M',
            'price' => 19.99
        ]);

        ProductVarient::create([
            'product_id' => 2, // Product ID for Blue Jeans
            'unit' => 'L',
            'price' => 39.99
        ]);
    }
}
