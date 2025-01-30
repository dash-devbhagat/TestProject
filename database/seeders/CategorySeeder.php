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
            'image' => null,
            'is_active' => true
        ]);

        Category::create([
            'name' => 'Clothing',
            'image' => null,
            'is_active' => true
        ]);

        Category::create([
            'name' => 'Home Appliances',
            'image' => null,
            'is_active' => true
        ]);
    }
}
