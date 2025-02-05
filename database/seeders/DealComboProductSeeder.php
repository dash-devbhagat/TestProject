<?php
namespace Database\Seeders;

use App\Models\DealComboProduct;
use Illuminate\Database\Seeder;

class DealComboProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Combo Deal: Burger & Coke Combo
        DealComboProduct::create([
            'deal_id' => 2, // Combo deal ID
            'product_id' => 1, // Product ID for Burger
            'variant_id' => 1, // Variant ID for Burger
            'quantity' => 1, // Quantity of Burger
        ]);

        DealComboProduct::create([
            'deal_id' => 2, // Combo deal ID
            'product_id' => 2, // Product ID for Coke
            'variant_id' => 4, // Variant ID for Coke
            'quantity' => 1, // Quantity of Coke
        ]);
    }
}