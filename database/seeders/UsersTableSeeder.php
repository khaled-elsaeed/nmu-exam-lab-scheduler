<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Faculty;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create the main admin
        $adminPassword = Str::random(8) . '@' . Str::random(4) . '1!';

        $admin = User::create([
            'email' => 'admin@nmu.edu.eg',
            'username' => 'Admin',
            'password' => bcrypt($adminPassword),
            'last_login' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->assignRole('admin'); 

        Log::info("Admin account created:", [
            'email' => $admin->email,
            'role' => 'admin',
            'password' => $adminPassword
        ]);

        $faculties = Faculty::all();

        foreach ($faculties as $faculty) {
            $password = Str::random(10) . 'A@1' . Str::random(5);

            // Create a faculty admin user
            $facultyAdmin = User::create([
                'email' => Str::slug($faculty->name) . '@nmu.edu.eg',
                'username' => $faculty->name,
                'password' => bcrypt($password),
                'last_login' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'faculty_id' => $faculty->id, 
            ]);

            $facultyAdmin->assignRole('faculty'); 

            Log::info("Faculty admin account created:", [
                'email' => $facultyAdmin->email,
                'roles' => ['faculty'],
                'password' => $password,
                'faculty_id' => $facultyAdmin->faculty_id
            ]);
        }
    }
}
