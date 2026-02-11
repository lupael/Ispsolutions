<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class CustomerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = [
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'A customer account.',
            'level' => 100,
            'permissions' => [
                'profile.view',
                'profile.update',
                'billing.view',
                'payments.view',
                'tickets.create',
                'tickets.view',
            ],
        ];

        Role::updateOrCreate(
            ['slug' => $role['slug']],
            $role
        );

        $this->command->info('Customer role seeded successfully!');
    }
}
