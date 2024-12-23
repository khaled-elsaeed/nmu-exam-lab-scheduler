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
        $admin = User::create([
            'email' => 'admin@nmu.edu.eg',
            'username' => 'Admin',
            'password' => bcrypt('admin'),
            'last_login' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->assignRole('admin'); 
    }
}
