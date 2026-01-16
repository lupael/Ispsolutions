<?php

namespace Database\Seeders;

use App\Models\ServicePackage;
use Illuminate\Database\Seeder;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic 5Mbps',
                'description' => 'Perfect for basic browsing and email',
                'bandwidth_up' => 1024,
                'bandwidth_down' => 5120,
                'price' => 25.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Standard 10Mbps',
                'description' => 'Great for streaming and remote work',
                'bandwidth_up' => 2048,
                'bandwidth_down' => 10240,
                'price' => 45.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Premium 20Mbps',
                'description' => 'For power users and small offices',
                'bandwidth_up' => 5120,
                'bandwidth_down' => 20480,
                'price' => 75.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
            [
                'name' => 'Business 50Mbps',
                'description' => 'Enterprise-grade connection',
                'bandwidth_up' => 10240,
                'bandwidth_down' => 51200,
                'price' => 150.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            ServicePackage::firstOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
