<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Nas;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nas>
 */
class NasFactory extends Factory
{
    protected $model = Nas::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => 'NAS-' . $this->faker->city(),
            'nas_name' => 'nas-' . $this->faker->domainWord(),
            'short_name' => strtoupper($this->faker->lexify('???')),
            'type' => $this->faker->randomElement(['mikrotik', 'cisco', 'other']),
            'ports' => $this->faker->numberBetween(1812, 1813),
            'secret' => $this->faker->password(12, 20),
            'server' => $this->faker->localIpv4(),
            'community' => 'public',
            'description' => $this->faker->sentence(),
            'status' => 'active',
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
}
