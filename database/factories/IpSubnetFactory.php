<?php

namespace Database\Factories;

use App\Models\IpPool;
use App\Models\IpSubnet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IpSubnet>
 */
class IpSubnetFactory extends Factory
{
    protected $model = IpSubnet::class;

    public function definition(): array
    {
        $thirdOctet = fake()->numberBetween(1, 254);

        return [
            'ip_pool_id' => IpPool::factory(),
            'network' => "192.168.{$thirdOctet}.0",
            'prefix_length' => 24,
            'gateway' => "192.168.{$thirdOctet}.1",
            'vlan_id' => fake()->optional()->numberBetween(1, 4094),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
