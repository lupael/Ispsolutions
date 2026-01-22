<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BandwidthUsage;
use App\Models\MikrotikRouter;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BandwidthUsage>
 */
class BandwidthUsageFactory extends Factory
{
    protected $model = BandwidthUsage::class;

    public function definition(): array
    {
        $uploadBytes = $this->faker->numberBetween(1048576, 10485760); // 1MB to 10MB
        $downloadBytes = $this->faker->numberBetween(2097152, 20971520); // 2MB to 20MB

        return [
            'monitorable_type' => 'App\\Models\\MikrotikRouter',
            'monitorable_id' => MikrotikRouter::factory(),
            'timestamp' => now(),
            'upload_bytes' => $uploadBytes,
            'download_bytes' => $downloadBytes,
            'total_bytes' => $uploadBytes + $downloadBytes,
            'period_type' => 'raw',
            'tenant_id' => null,
        ];
    }

    public function rawPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_type' => 'raw',
        ]);
    }

    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_type' => 'hourly',
            'timestamp' => Carbon::now()->startOfHour(),
        ]);
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_type' => 'daily',
            'timestamp' => Carbon::now()->startOfDay(),
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_type' => 'weekly',
            'timestamp' => Carbon::now()->startOfWeek(),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_type' => 'monthly',
            'timestamp' => Carbon::now()->startOfMonth(),
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

    public function atTimestamp(Carbon $timestamp): static
    {
        return $this->state(fn (array $attributes) => [
            'timestamp' => $timestamp,
        ]);
    }
}
