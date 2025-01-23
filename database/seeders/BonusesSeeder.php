<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bonuses')->insert([
            [
                'type' => 'signup',
                'amount' => 100,
                'percentage' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'referral',
                'amount' => 50,
                'percentage' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
