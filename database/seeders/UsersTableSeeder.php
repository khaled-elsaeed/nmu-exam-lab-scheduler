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

        // Get all faculties
        $faculties = Faculty::all();
        foreach ($faculties as $faculty) {
            $facultyName = str_replace('&', 'and', $faculty->name);
            $roleName = strtolower(str_replace(' ', '-', $facultyName));

            $password = Str::random(10) . 'A@1' . Str::random(5);

            $user = User::create([
                'email' => Str::slug($faculty->name) . '@nmu.edu.eg',
                'username' => $faculty->name,
                'password' => bcrypt($password),
                'last_login' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->assignRole('faculty');
            $user->assignRole($roleName);

            Log::info("Faculty account created:", [
                'email' => $user->email,
                'roles' => ['faculty', $roleName],
                'password' => $password
            ]);
        }
    }
}






