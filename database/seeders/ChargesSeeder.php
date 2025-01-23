<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('charges')->insert([
            [
                'name' => 'SGST',
                'type' => 'percentage',
                'value' => 9.00, // Replace with the actual percentage
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CGST',
                'type' => 'percentage',
                'value' => 9.00, // Replace with the actual percentage
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delivery Charge',
                'type' => 'fixed',
                'value' => 20.00, // Replace with the actual fixed amount
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
