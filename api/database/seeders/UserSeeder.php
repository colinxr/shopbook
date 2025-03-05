<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

/**
 * User Seeder
 * 
 * Creates default users for development and testing.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create artist user
        User::create([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Artist User',
            'email' => 'artist@example.com',
            'password' => Hash::make('password'),
            'role' => 'artist',
            'phone' => '555-123-4567',
            'email_verified_at' => now(),
        ]);

        // Create client user
        User::create([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'phone' => '555-987-6543',
            'email_verified_at' => now(),
        ]);

        // Create additional random users
        User::factory()->count(5)->create();
        User::factory()->artist()->count(3)->create();
    }
}
