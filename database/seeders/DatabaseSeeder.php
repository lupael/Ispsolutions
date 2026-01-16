<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed service packages first
        $this->call([
            ServicePackageSeeder::class,
            IpPoolSeeder::class,
            IpSubnetSeeder::class,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        // Create additional test users with packages
        User::factory(10)->create([
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }
}
