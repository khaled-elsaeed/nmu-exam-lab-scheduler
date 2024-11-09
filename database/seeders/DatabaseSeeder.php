<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; 
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UsersTableSeeder; 
use Database\Seeders\FacultiesTableSeeder; 
use Database\Seeders\LabSeeder; 
use Database\Seeders\ExamSettingsSeeder; 




class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FacultiesTableSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(LabSeeder::class);
        $this->call(ExamSettingsSeeder::class);
    }
}
