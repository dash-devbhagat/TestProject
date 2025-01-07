<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\State;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $maharashtra = State::where('name', 'Maharashtra')->first();
        $gujarat = State::where('name', 'Gujarat')->first();
        $rajasthan = State::where('name', 'Rajasthan')->first();
        $uttarPradesh = State::where('name', 'Uttar Pradesh')->first();
        $karnataka = State::where('name', 'Karnataka')->first();

        $cities = [
            ['name' => 'Mumbai', 'state_id' => $maharashtra->id],
            ['name' => 'Pune', 'state_id' => $maharashtra->id],
            ['name' => 'Ahmedabad', 'state_id' => $gujarat->id],
            ['name' => 'Surat', 'state_id' => $gujarat->id],
            ['name' => 'Vadodara', 'state_id' => $gujarat->id],
            ['name' => 'Jaipur', 'state_id' => $rajasthan->id],
            ['name' => 'Udaipur', 'state_id' => $rajasthan->id],
            ['name' => 'Kanpur', 'state_id' => $uttarPradesh->id],
            ['name' => 'Varanasi', 'state_id' => $uttarPradesh->id],
            ['name' => 'Bangalore', 'state_id' => $karnataka->id],
        ];

        foreach ($cities as $cityData) {
            City::create($cityData);
        }
    }
}
