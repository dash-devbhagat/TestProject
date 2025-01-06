<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Category::create([
            'name' => 'Electronics',
            'image' => 'path_to_image',
            'is_active' => true
        ]);

        Category::create([
            'name' => 'Clothing',
            'image' => 'path_to_image',
            'is_active' => true
        ]);

        Category::create([
            'name' => 'Home Appliances',
            'image' => 'path_to_image',
            'is_active' => true
        ]);
    }
}
