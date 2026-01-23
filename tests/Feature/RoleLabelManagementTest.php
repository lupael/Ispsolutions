<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleLabelSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleLabelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 20,
        ]);

        // Assign admin role
        $adminRole = Role::where('slug', 'admin')->first();
        $this->admin->roles()->attach($adminRole);

        // Ensure operator and sub-operator roles exist
        if (!Role::where('slug', 'operator')->exists()) {
            Role::create([
                'slug' => 'operator',
                'name' => 'Operator',
                'description' => 'Operator role',
                'operator_level' => 30,
            ]);
        }

        if (!Role::where('slug', 'sub-operator')->exists()) {
            Role::create([
                'slug' => 'sub-operator',
                'name' => 'Sub-Operator',
                'description' => 'Sub-Operator role',
                'operator_level' => 40,
            ]);
        }
    }

    /** @test */
    public function admin_can_view_role_label_settings_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('panel.admin.settings.role-labels'));

        $response->assertOk();
        $response->assertViewIs('panels.admin.settings.role-labels');
        $response->assertViewHas('customizableRoles');
        $response->assertViewHas('settings');
    }

    /** @test */
    public function admin_can_set_custom_operator_label()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'operator',
                'custom_label' => 'Partner',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('role_label_settings', [
            'tenant_id' => $this->tenant->id,
            'role_slug' => 'operator',
            'custom_label' => 'Partner',
        ]);
    }

    /** @test */
    public function admin_can_set_custom_sub_operator_label()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'sub-operator',
                'custom_label' => 'Sub-Agent',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('role_label_settings', [
            'tenant_id' => $this->tenant->id,
            'role_slug' => 'sub-operator',
            'custom_label' => 'Sub-Agent',
        ]);
    }

    /** @test */
    public function admin_can_update_existing_custom_label()
    {
        // Create initial label
        RoleLabelSetting::setCustomLabel($this->tenant->id, 'operator', 'Partner');

        $response = $this->actingAs($this->admin)
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'operator',
                'custom_label' => 'Agent',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('role_label_settings', [
            'tenant_id' => $this->tenant->id,
            'role_slug' => 'operator',
            'custom_label' => 'Agent',
        ]);
    }

    /** @test */
    public function admin_can_remove_custom_label()
    {
        // Create initial label
        RoleLabelSetting::setCustomLabel($this->tenant->id, 'operator', 'Partner');

        $response = $this->actingAs($this->admin)
            ->from(route('panel.admin.settings.role-labels'))
            ->delete(route('panel.admin.settings.role-labels.destroy', 'operator'));

        $response->assertRedirect(route('panel.admin.settings.role-labels'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('role_label_settings', [
            'tenant_id' => $this->tenant->id,
            'role_slug' => 'operator',
        ]);
    }

    /** @test */
    public function admin_can_remove_custom_label_by_submitting_empty_value()
    {
        // Create initial label
        RoleLabelSetting::setCustomLabel($this->tenant->id, 'operator', 'Partner');

        // Submit empty custom_label to remove it
        $response = $this->actingAs($this->admin)
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'operator',
                'custom_label' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Custom role label removed. Using default label.');

        $this->assertDatabaseMissing('role_label_settings', [
            'tenant_id' => $this->tenant->id,
            'role_slug' => 'operator',
        ]);
    }

    /** @test */
    public function custom_label_is_displayed_for_users()
    {
        // Set custom label
        RoleLabelSetting::setCustomLabel($this->tenant->id, 'operator', 'Partner');

        // Create operator user
        $operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 30,
        ]);
        $operatorRole = Role::where('slug', 'operator')->first();
        $operator->roles()->attach($operatorRole);

        // Check that custom label is returned
        $displayLabel = $operator->getRoleDisplayLabel();
        $this->assertEquals('Partner', $displayLabel);
    }

    /** @test */
    public function default_label_is_used_when_no_custom_label_exists()
    {
        // Create operator user without custom label
        $operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 30,
        ]);
        $operatorRole = Role::where('slug', 'operator')->first();
        $operator->roles()->attach($operatorRole);

        // Check that default label is returned
        $displayLabel = $operator->getRoleDisplayLabel();
        $this->assertEquals('Operator', $displayLabel);
    }

    /** @test */
    public function custom_labels_are_tenant_scoped()
    {
        // Create another tenant
        $tenant2 = Tenant::factory()->create();

        // Set custom label for first tenant
        RoleLabelSetting::setCustomLabel($this->tenant->id, 'operator', 'Partner');

        // Set different custom label for second tenant
        RoleLabelSetting::setCustomLabel($tenant2->id, 'operator', 'Agent');

        // Create operators for each tenant
        $operator1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'operator_level' => 30,
        ]);
        $operator2 = User::factory()->create([
            'tenant_id' => $tenant2->id,
            'operator_level' => 30,
        ]);

        $operatorRole = Role::where('slug', 'operator')->first();
        $operator1->roles()->attach($operatorRole);
        $operator2->roles()->attach($operatorRole);

        // Check that each operator sees their tenant's custom label
        $this->assertEquals('Partner', $operator1->getRoleDisplayLabel());
        $this->assertEquals('Agent', $operator2->getRoleDisplayLabel());
    }

    /** @test */
    public function validation_fails_for_invalid_role_slug()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('panel.admin.settings.role-labels'))
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'invalid-role',
                'custom_label' => 'Test',
            ]);

        $response->assertStatus(302); // Redirect back with errors
    }

    /** @test */
    public function validation_fails_for_too_long_custom_label()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('panel.admin.settings.role-labels'))
            ->put(route('panel.admin.settings.role-labels.update'), [
                'role_slug' => 'operator',
                'custom_label' => str_repeat('a', 51), // 51 characters
            ]);

        $response->assertStatus(302); // Redirect back with errors
    }
}
