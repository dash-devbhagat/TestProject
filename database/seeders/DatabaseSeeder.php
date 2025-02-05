<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SubCategorySeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            BonusesSeeder::class,
            ChargesSeeder::class,
            BranchesTableSeeder::class,
            TimingsTableSeeder::class,
            DealSeeder::class,
            DealComboProductSeeder::class,
        ]);
    }
}
