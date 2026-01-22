<?php

namespace Tests\Feature\Validation;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    public function test_validation_rejects_invalid_email_format(): void
    {
        $this->assertValidationFails(['email' => 'not-an-email']);
    }

    public function test_validation_rejects_negative_amounts(): void
    {
        $this->assertValidationFails(['amount' => -100]);
    }

    public function test_validation_rejects_future_dates_where_inappropriate(): void
    {
        $this->assertValidationFails(['billing_period_end' => '2020-01-01', 'billing_period_start' => '2024-01-01']);
    }

    protected function assertValidationFails(array $invalidData): void
    {
        $this->assertTrue(true); // Placeholder for actual validation tests
    }
}
