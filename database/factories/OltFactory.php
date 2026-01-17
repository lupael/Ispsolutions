<?php

namespace Database\Factories;

use App\Models\Olt;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Olt>
 */
class OltFactory extends Factory
{
    protected $model = Olt::class;

    public function definition(): array
    {
        return [
            'name' => 'OLT-' . fake()->city(),
            'ip_address' => fake()->localIpv4(),
            'port' => 23,
            'management_protocol' => fake()->randomElement(['ssh', 'telnet', 'snmp']),
            'username' => 'admin',
            'password' => 'password123',
            'snmp_community' => fake()->optional()->randomElement(['public', 'private']),
            'snmp_version' => fake()->optional()->randomElement(['v1', 'v2c', 'v3']),
            'model' => fake()->randomElement(['Huawei MA5608T', 'ZTE C320', 'Huawei MA5800', 'ZTE C600']),
            'location' => fake()->address(),
            'status' => 'active',
            'health_status' => 'unknown',
            'last_backup_at' => null,
            'last_health_check_at' => null,
            'tenant_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    public function withTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
        ]);
    }

    public function healthy(): static
    {
        return $this->state(fn (array $attributes) => [
            'health_status' => 'ok',
            'last_health_check_at' => now(),
        ]);
    }
}
