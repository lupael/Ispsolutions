<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MikrotikRouter;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MikrotikRouter>
 */
class MikrotikRouterFactory extends Factory
{
    protected $model = MikrotikRouter::class;

    public function definition(): array
    {
        return [
            'name' => 'Router-' . fake()->city(),
            'ip_address' => fake()->localIpv4(),
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password123',
            'status' => 'active',
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

    public function withTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
        ]);
    }
}
