<?php

namespace Database\Seeders;

use App\Authorization\AuthorizationRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create main admin user (not linked to any specific model)
        $adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@library.com',
            'phone' => '+1234567890',
            'address' => '123 Library Street, Book City',
            'birth_date' => '1980-01-01',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'userable_id' => null,
            'userable_type' => null,
        ]);

        // Assign admin role
        $adminRole = Role::where('name', AuthorizationRole::ADMIN)->first();
        if ($adminRole) {
            $adminUser->roles()->attach($adminRole);
        }
    }
}
