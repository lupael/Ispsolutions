<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        $commissionPercentage = $this->faker->randomFloat(2, 5, 20);
        $paymentAmount = $this->faker->randomFloat(2, 100, 1000);
        $commissionAmount = ($paymentAmount * $commissionPercentage) / 100;

        return [
            'tenant_id' => 1,
            // Note: reseller_id field name kept for backward compatibility, refers to operator_id
            'reseller_id' => User::factory(),
            'payment_id' => Payment::factory(),
            'invoice_id' => Invoice::factory(),
            'commission_amount' => $commissionAmount,
            'commission_percentage' => $commissionPercentage,
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'paid_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }
}
