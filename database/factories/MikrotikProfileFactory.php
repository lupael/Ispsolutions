<?php

namespace Database\Factories;

use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use Illuminate\Database\Eloquent\Factories\Factory;

class MikrotikProfileFactory extends Factory
{
    protected $model = MikrotikProfile::class;

    public function definition(): array
    {
        return [
            'router_id' => MikrotikRouter::factory(),
            'name' => $this->faker->unique()->word . '_profile',
            'local_address' => $this->faker->localIpv4(),
            'remote_address' => $this->faker->ipv4(),
            'rate_limit' => '10M/10M',
            'session_timeout' => '0',
        ];
    }
}
