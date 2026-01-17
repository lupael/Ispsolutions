<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeviceMonitor;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceMonitor>
 */
class DeviceMonitorFactory extends Factory
{
    protected $model = DeviceMonitor::class;

    public function definition(): array
    {
        return [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => MikrotikRouter::factory(),
            'status' => fake()->randomElement(['online', 'offline', 'degraded', 'unknown']),
            'cpu_usage' => fake()->randomFloat(2, 0, 100),
            'memory_usage' => fake()->randomFloat(2, 0, 100),
            'uptime' => fake()->numberBetween(0, 2592000), // 0 to 30 days in seconds
            'last_check_at' => now(),
            'tenant_id' => null,
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'online',
            'cpu_usage' => fake()->randomFloat(2, 0, 80),
            'memory_usage' => fake()->randomFloat(2, 0, 80),
            'uptime' => fake()->numberBetween(3600, 2592000),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'offline',
            'cpu_usage' => null,
            'memory_usage' => null,
            'uptime' => null,
        ]);
    }

    public function degraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'degraded',
            'cpu_usage' => fake()->randomFloat(2, 80, 100),
            'memory_usage' => fake()->randomFloat(2, 80, 100),
        ]);
    }

    public function forRouter(): static
    {
        return $this->state(fn (array $attributes) => [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => MikrotikRouter::factory(),
        ]);
    }

    public function forOlt(): static
    {
        return $this->state(fn (array $attributes) => [
            'monitorable_type' => 'App\\Models\\Olt',
            'monitorable_id' => Olt::factory(),
        ]);
    }

    public function forOnu(): static
    {
        return $this->state(fn (array $attributes) => [
            'monitorable_type' => 'App\\Models\\Onu',
            'monitorable_id' => Onu::factory(),
        ]);
    }

    public function withTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
        ]);
    }
}
