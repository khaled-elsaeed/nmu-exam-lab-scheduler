<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('labs')->insert([
            ['building' => '5', 'floor' => '3', 'number' => '8', 'capacity' => 21],
            ['building' => '5', 'floor' => '3', 'number' => '107', 'capacity' => 30],
            ['building' => '5', 'floor' => '2', 'number' => '6', 'capacity' => 25],
            ['building' => '5', 'floor' => '3', 'number' => '10', 'capacity' => 24],
            ['building' => '5', 'floor' => '2', 'number' => '13', 'capacity' => 12],
            ['building' => '5', 'floor' => '2', 'number' => '14', 'capacity' => 24],
            ['building' => '5', 'floor' => '3', 'number' => '9', 'capacity' => 12],
            ['building' => '5', 'floor' => '2', 'number' => '22', 'capacity' => 23],
            ['building' => '2', 'floor' => '1', 'number' => '52', 'capacity' => 58],
            ['building' => '2', 'floor' => '1', 'number' => '40', 'capacity' => 23],
            ['building' => '2', 'floor' => '0', 'number' => '25', 'capacity' => 24],
            ['building' => '2', 'floor' => '2', 'number' => '81', 'capacity' => 23],
            ['building' => '2', 'floor' => '2', 'number' => '34', 'capacity' => 22],
            ['building' => '2', 'floor' => '1', 'number' => '41', 'capacity' => 7],
            ['building' => '7', 'floor' => '3', 'number' => '149', 'capacity' => 20],
            ['building' => '7', 'floor' => '3', 'number' => '54', 'capacity' => 28],
            ['building' => '7', 'floor' => '3', 'number' => '147', 'capacity' => 30],
            ['building' => '7', 'floor' => '2', 'number' => '23', 'capacity' => 25],
            ['building' => '7', 'floor' => '2', 'number' => '58', 'capacity' => 31],
            ['building' => '7', 'floor' => '2', 'number' => '150', 'capacity' => 27],
        ]);
    }
}
