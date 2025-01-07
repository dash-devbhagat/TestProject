<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $states = [
            ['name' => 'Maharashtra'],
            ['name' => 'Gujarat'],
            ['name' => 'Rajasthan'],
            ['name' => 'Uttar Pradesh'],
            ['name' => 'Karnataka'],
        ];

        foreach ($states as $stateData) {
            State::create($stateData);
        }
    }
}
