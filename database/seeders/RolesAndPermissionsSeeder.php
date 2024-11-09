<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Faculty;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $facultyRole = Role::firstOrCreate(['name' => 'faculty']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $faculties = Faculty::all();
        foreach ($faculties as $faculty) {
            $facultyName = str_replace('&', 'and', $faculty->name);
            $roleName = strtolower(str_replace(' ', '-', $facultyName));

            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}
