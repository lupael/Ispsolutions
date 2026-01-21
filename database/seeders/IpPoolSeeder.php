<?php

namespace Database\Seeders;

use App\Models\IpPool;
use Illuminate\Database\Seeder;

class IpPoolSeeder extends Seeder
{
    public function run(): void
    {
        $pools = [
            [
                'name' => 'Public Pool 1',
                'description' => 'Main public IP address pool',
                'pool_type' => 'public',
                'status' => 'active',
            ],
            [
                'name' => 'Private Pool 1',
                'description' => 'Private network addresses for internal use',
                'pool_type' => 'private',
                'status' => 'active',
            ],
        ];

        foreach ($pools as $pool) {
            IpPool::firstOrCreate(
                ['name' => $pool['name']],
                $pool
            );
        }
    }
}
