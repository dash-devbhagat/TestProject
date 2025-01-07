<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create example products
        Product::create([
            'sku' => 'PROD-1234-RED-M',
            'name' => 'Red T-Shirt',
            'image' => 'path_to_image',
            'is_active' => true,
            'category_id' => 2, // ID for Clothing
            'sub_category_id' => 4, // ID for Women\'s Clothing
            'details' => 'A comfortable red t-shirt made of cotton fabric.',
        ]);

        Product::create([
            'sku' => 'PROD-5678-BLACK-L',
            'name' => 'Black Jacket',
            'image' => 'path_to_image',
            'is_active' => true,
            'category_id' => 2, // ID for Clothing
            'sub_category_id' => 4, // ID for Women\'s Clothing
            'details' => 'A stylish black jacket with a zipper closure.',
        ]);

        Product::create([
            'sku' => 'PROD-9012-SILVER-16GB',
            'name' => 'Silver Laptop 16GB',
            'image' => 'path_to_image',
            'is_active' => true,
            'category_id' => 1, // ID for Electronics
            'sub_category_id' => 2, // ID for Laptops
            'details' => 'A powerful laptop with 16GB RAM and 512GB SSD.',
        ]);
    }
}
