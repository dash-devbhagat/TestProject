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
        Product::create([
            'sku' => 'PROD-1234-RED-M',
            'name' => 'Red T-shirt',
            'image' => 'path_to_image',
            'is_active' => true
        ]);

        Product::create([
            'sku' => 'PROD-5678-BLUE-L',
            'name' => 'Blue Jeans',
            'image' => 'path_to_image',
            'is_active' => true
        ]);
    }
}
