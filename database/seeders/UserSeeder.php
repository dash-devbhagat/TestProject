<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insert the admin user
        DB::table('users')->insert([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@yopmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'phone' => '1234567890',
            'storename' => 'Admin Store',
            'location' => '123 Admin Street, Admin City',
            'latitude' => 37.774929, // Example latitude
            'longitude' => -122.419418, // Example longitude
            'logo' => 'admin-logo.png',
            'isProfile' => true,
            'isDelete' => false,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert 19 regular users
        for ($i = 1; $i <= 19; $i++) {
            DB::table('users')->insert([
                'role' => 'user',
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@yopmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'phone' => '98765432' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'storename' => 'Store ' . $i,
                'location' => 'Location ' . $i,
                'latitude' => mt_rand(-90 * 100000000, 90 * 100000000) / 100000000, // Random latitude
                'longitude' => mt_rand(-180 * 100000000, 180 * 100000000) / 100000000, // Random longitude
                'logo' => 'user' . $i . '-logo.png',
                'isProfile' => (bool)rand(0, 1),
                'isDelete' => false,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
