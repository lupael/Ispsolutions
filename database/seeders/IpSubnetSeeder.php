<?php

namespace Database\Seeders;

use App\Models\IpPool;
use App\Models\IpSubnet;
use Illuminate\Database\Seeder;

class IpSubnetSeeder extends Seeder
{
    public function run(): void
    {
        $publicPool = IpPool::where('pool_type', 'public')->first();
        $privatePool = IpPool::where('pool_type', 'private')->first();

        if ($publicPool) {
            IpSubnet::firstOrCreate(
                ['network' => '203.0.113.0', 'prefix_length' => 24],
                [
                    'ip_pool_id' => $publicPool->id,
                    'gateway' => '203.0.113.1',
                    'vlan_id' => 100,
                    'description' => 'Public subnet for customer connections',
                    'status' => 'active',
                ]
            );
        }

        if ($privatePool) {
            IpSubnet::firstOrCreate(
                ['network' => '192.168.100.0', 'prefix_length' => 24],
                [
                    'ip_pool_id' => $privatePool->id,
                    'gateway' => '192.168.100.1',
                    'vlan_id' => 200,
                    'description' => 'Private subnet for internal network',
                    'status' => 'active',
                ]
            );

            IpSubnet::firstOrCreate(
                ['network' => '192.168.101.0', 'prefix_length' => 24],
                [
                    'ip_pool_id' => $privatePool->id,
                    'gateway' => '192.168.101.1',
                    'vlan_id' => 201,
                    'description' => 'Private subnet for management',
                    'status' => 'active',
                ]
            );
        }
    }
}
