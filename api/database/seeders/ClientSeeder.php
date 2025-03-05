<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Client Seeder
 * 
 * Creates test clients for development and testing.
 */
class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create clients with user accounts
        $clientUsers = User::where('role', 'client')->get();
        foreach ($clientUsers as $user) {
            Client::factory()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
        }

        // Create additional clients without user accounts
        Client::factory()->count(10)->create();
    }
}
