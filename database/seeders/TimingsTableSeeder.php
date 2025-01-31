<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimingsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('timings')->insert([
            [
                'branch_id' => 1,  // Reference to Main Branch (use the actual ID)
                'day' => 'Monday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => 1,  // Reference to Main Branch
                'day' => 'Tuesday',
                'opening_time' => '09:00:00',
                'closing_time' => '17:00:00',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more timings for each day and branch as needed
        ]);
    }
}
