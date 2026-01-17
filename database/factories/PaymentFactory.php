<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'payment_number' => 'PAY-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
            'payment_gateway_id' => null,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'transaction_id' => $this->faker->optional()->uuid(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_method' => $this->faker->randomElement(['gateway', 'card', 'cash', 'bank_transfer']),
            'payment_data' => null,
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
