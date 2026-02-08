<?php

namespace Tests\Feature\Panel;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWizardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $tenancyService = app(TenancyService::class);
        $tenancyService->setCurrentTenant($this->tenant);

        $this->actingAs($this->user);
    }

    public function test_customer_wizard_can_be_started()
    {
        $response = $this->get(route('panel.admin.customers.wizard.start'));

        $response->assertStatus(302);
        $response->assertRedirect(route('panel.admin.customers.wizard.step', ['step' => 1]));
        $this->assertDatabaseHas('temp_customers', [
            'user_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
            'step' => 1,
        ]);
    }
}
