<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubCategory::create([
            'category_id' => 1, // Category ID for Electronics
            'name' => 'Mobiles',
            'image' => null,
            'is_active' => true
        ]);

        SubCategory::create([
            'category_id' => 1, // Category ID for Electronics
            'name' => 'Laptops',
            'image' => null,
            'is_active' => true
        ]);

        SubCategory::create([
            'category_id' => 2, // Category ID for Clothing
            'name' => 'Men\'s Clothing',
            'image' => null,
            'is_active' => true
        ]);

        SubCategory::create([
            'category_id' => 2, // Category ID for Clothing
            'name' => 'Women\'s Clothing',
            'image' => null,
            'is_active' => true
        ]);
    }
}
