<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the three system roles referenced throughout the SDD:
 * Student, Lecturer, Administrator (SDD 4.2 "Role" table).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Student', 'Lecturer', 'Administrator'] as $roleName) {
            Role::firstOrCreate(
                ['role_name' => $roleName],
                ['description' => "{$roleName} role", 'permissions' => null]
            );
        }
    }
}
