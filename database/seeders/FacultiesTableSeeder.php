<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Faculty data
        $faculties = [
            ['name' => 'Business'],
            ['name' => 'Law'],
            ['name' => 'Engineering'],
            ['name' => 'Computer Science & Engineering'],
            ['name' => 'Science'],
            ['name' => 'Medicine'],
            ['name' => 'Dentistry'],
            ['name' => 'Pharmacy'],
            ['name' => 'Applied Health Sciences Technology'],
            ['name' => 'Nursing'],
            ['name' => 'Textile Science Engineering'],
            ['name' => 'Social & Human Sciences'],
            ['name' => 'Mass Media & Communication'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create([
                'name' => $faculty['name'], 
            ]);
        }
    }
}
