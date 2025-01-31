<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('branches')->insert([
            [
                'name' => 'Main Branch',
                'address' => '123 Main St, City, Country',
                'logo' => null,
                'description' => 'Main branch located in the city center.',
                'latitude' => 23.0123997,
                'longitude' => 72.5109465,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Branch 2',
                'address' => '456 Another St, City, Country',
                'logo' => null,
                'description' => 'Second branch in the suburbs.',
                'latitude' => 23.0150819,
                'longitude' => 72.5310250,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more branches as needed
        ]);
    }
}

