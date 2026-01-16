<?php

namespace Database\Factories;

use App\Models\IpAllocation;
use App\Models\IpSubnet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IpAllocation>
 */
class IpAllocationFactory extends Factory
{
    protected $model = IpAllocation::class;

    public function definition(): array
    {
        $lastOctet = fake()->numberBetween(2, 254);
        
        return [
            'ip_subnet_id' => IpSubnet::factory(),
            'ip_address' => "192.168.1.{$lastOctet}",
            'user_id' => User::factory(),
            'allocation_type' => 'dynamic',
            'status' => 'active',
            'allocated_at' => now(),
            'released_at' => null,
            'expires_at' => now()->addDay(),
        ];
    }

    public function static(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocation_type' => 'static',
            'expires_at' => null,
        ]);
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'released',
            'released_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
