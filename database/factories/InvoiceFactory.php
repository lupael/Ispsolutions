<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 100, 5000);
        $taxAmount = $amount * 0.15; // 15% tax

        return [
            'tenant_id' => 1,
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'package_id' => ServicePackage::factory(),
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $amount + $taxAmount,
            'status' => $this->faker->randomElement(['draft', 'pending', 'paid', 'cancelled', 'overdue']),
            'billing_period_start' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'billing_period_end' => $this->faker->dateTimeBetween('now', '+30 days'),
            'due_date' => $this->faker->dateTimeBetween('+5 days', '+15 days'),
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
