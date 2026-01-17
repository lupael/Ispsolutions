<?php

namespace Database\Factories;

use App\Models\NetworkUser;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Onu>
 */
class OnuFactory extends Factory
{
    protected $model = Onu::class;

    public function definition(): array
    {
        return [
            'olt_id' => Olt::factory(),
            'pon_port' => '0/' . fake()->numberBetween(1, 16) . '/' . fake()->numberBetween(1, 16),
            'onu_id' => fake()->numberBetween(1, 128),
            'serial_number' => strtoupper(fake()->bothify('HWTC########')),
            'mac_address' => fake()->macAddress(),
            'network_user_id' => null,
            'name' => fake()->optional()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'status' => 'unknown',
            'signal_rx' => null,
            'signal_tx' => null,
            'distance' => null,
            'ipaddress' => null,
            'last_seen_at' => null,
            'last_sync_at' => null,
            'tenant_id' => null,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'online',
            'signal_rx' => fake()->randomFloat(2, -30, -15),
            'signal_tx' => fake()->randomFloat(2, 0, 5),
            'distance' => fake()->numberBetween(100, 20000),
            'ipaddress' => fake()->localIpv4(),
            'last_seen_at' => now(),
            'last_sync_at' => now(),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'offline',
            'last_seen_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }

    public function los(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'los',
            'signal_rx' => null,
            'signal_tx' => null,
        ]);
    }

    public function withNetworkUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'network_user_id' => NetworkUser::factory(),
        ]);
    }

    public function withTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
        ]);
    }
}
