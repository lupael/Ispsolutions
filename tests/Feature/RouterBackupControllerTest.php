<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MikrotikRouter;
use App\Models\Role;
use App\Models\RouterConfigurationBackup;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RouterBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RouterBackupControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;
    private MikrotikRouter $router;

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
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $this->admin->roles()->attach($adminRole);
        }

        // Create router
        $this->router = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test admin can create backup.
     */
    public function test_admin_can_create_backup(): void
    {
        $this->actingAs($this->admin);

        // Create a mock backup record
        $mockBackup = new RouterConfigurationBackup([
            'id' => 1,
            'router_id' => $this->router->id,
            'backup_type' => 'manual',
            'notes' => 'Test Backup',
            'created_by' => $this->admin->id,
        ]);
        $mockBackup->id = 1;
        $mockBackup->created_at = now();

        // Mock the RouterBackupService
        $mockService = Mockery::mock(RouterBackupService::class);
        $mockService->shouldReceive('createManualBackup')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                'Test Backup',
                'Manual backup for testing',
                $this->admin->id
            )
            ->andReturn($mockBackup);

        $this->app->instance(RouterBackupService::class, $mockService);

        $response = $this->post(route('panel.admin.routers.backup.create', $this->router->id), [
            'name' => 'Test Backup',
            'reason' => 'Manual backup for testing',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Backup created successfully.',
        ]);
    }

    /**
     * Test admin can list backups.
     */
    public function test_admin_can_list_backups(): void
    {
        $this->actingAs($this->admin);

        // Create mock backups
        $backups = collect([
            (object) [
                'id' => 1,
                'backup_type' => 'manual',
                'notes' => 'Backup 1',
                'created_at' => now()->subDays(2),
                'created_by' => $this->admin->id,
            ],
            (object) [
                'id' => 2,
                'backup_type' => 'automatic',
                'notes' => 'Backup 2',
                'created_at' => now()->subDay(),
                'created_by' => $this->admin->id,
            ],
        ]);

        // Mock the RouterBackupService
        $mockService = Mockery::mock(RouterBackupService::class);
        $mockService->shouldReceive('listBackups')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                null
            )
            ->andReturn($backups);

        $this->app->instance(RouterBackupService::class, $mockService);

        $response = $this->get(route('panel.admin.routers.backup.list', $this->router->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(2, 'backups');
    }

    /**
     * Test admin can restore backup.
     */
    public function test_admin_can_restore_backup(): void
    {
        $this->actingAs($this->admin);

        // Create a real backup in database
        $backup = RouterConfigurationBackup::create([
            'router_id' => $this->router->id,
            'backup_type' => 'manual',
            'configuration_data' => json_encode(['test' => 'data']),
            'notes' => 'Test backup',
            'created_by' => $this->admin->id,
        ]);

        // Mock the RouterBackupService
        $mockService = Mockery::mock(RouterBackupService::class);
        $mockService->shouldReceive('restoreFromBackup')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                Mockery::on(fn ($b) => $b->id === $backup->id)
            )
            ->andReturn(true);

        $this->app->instance(RouterBackupService::class, $mockService);

        $response = $this->post(route('panel.admin.routers.backup.restore', $this->router->id), [
            'backup_id' => $backup->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Configuration restored successfully.',
        ]);
    }

    /**
     * Test admin can delete backup.
     */
    public function test_admin_can_delete_backup(): void
    {
        $this->actingAs($this->admin);

        // Create a backup in database
        $backup = RouterConfigurationBackup::create([
            'router_id' => $this->router->id,
            'backup_type' => 'manual',
            'configuration_data' => json_encode(['test' => 'data']),
            'notes' => 'Test backup to delete',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->delete(route('panel.admin.routers.backup.destroy', [
            'router' => $this->router->id,
            'backup' => $backup->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Backup deleted successfully.',
        ]);

        $this->assertDatabaseMissing('router_configuration_backups', [
            'id' => $backup->id,
        ]);
    }

    /**
     * Test backup tenant isolation.
     */
    public function test_backup_tenant_isolation(): void
    {
        $this->actingAs($this->admin);

        // Create router and backup for another tenant
        $otherTenant = Tenant::factory()->create();
        $otherRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $otherBackup = RouterConfigurationBackup::create([
            'router_id' => $otherRouter->id,
            'backup_type' => 'manual',
            'configuration_data' => json_encode(['test' => 'data']),
            'notes' => 'Other tenant backup',
            'created_by' => 1,
        ]);

        // Try to list backups for router from another tenant
        $response = $this->get(route('panel.admin.routers.backup.list', $otherRouter->id));
        $response->assertNotFound();

        // Try to restore backup for router from another tenant
        $response = $this->post(route('panel.admin.routers.backup.restore', $otherRouter->id), [
            'backup_id' => $otherBackup->id,
        ]);
        $response->assertNotFound();

        // Try to delete backup for router from another tenant
        $response = $this->delete(route('panel.admin.routers.backup.destroy', [
            'router' => $otherRouter->id,
            'backup' => $otherBackup->id,
        ]));
        $response->assertNotFound();

        // Verify backup still exists
        $this->assertDatabaseHas('router_configuration_backups', [
            'id' => $otherBackup->id,
        ]);
    }

    /**
     * Test restore requires valid backup.
     */
    public function test_restore_requires_valid_backup(): void
    {
        $this->actingAs($this->admin);

        // Try to restore with non-existent backup ID
        $response = $this->post(route('panel.admin.routers.backup.restore', $this->router->id), [
            'backup_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['backup_id']);

        // Create backup for a different router
        $anotherRouter = MikrotikRouter::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $backupForAnotherRouter = RouterConfigurationBackup::create([
            'router_id' => $anotherRouter->id,
            'backup_type' => 'manual',
            'configuration_data' => json_encode(['test' => 'data']),
            'notes' => 'Another router backup',
            'created_by' => $this->admin->id,
        ]);

        // Try to restore backup that belongs to another router
        $response = $this->post(route('panel.admin.routers.backup.restore', $this->router->id), [
            'backup_id' => $backupForAnotherRouter->id,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Backup does not belong to this router.',
        ]);
    }

    /**
     * Test cleanup old backups.
     */
    public function test_cleanup_old_backups(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterBackupService
        $mockService = Mockery::mock(RouterBackupService::class);
        $mockService->shouldReceive('cleanupOldBackups')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                30
            )
            ->andReturn(5); // 5 backups deleted

        $this->app->instance(RouterBackupService::class, $mockService);

        $response = $this->post(route('panel.admin.routers.backup.cleanup', $this->router->id), [
            'retention_days' => 30,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Deleted 5 old backup(s).',
            'deleted_count' => 5,
        ]);
    }

    /**
     * Test cleanup uses default retention days when not specified.
     */
    public function test_cleanup_uses_default_retention_days(): void
    {
        $this->actingAs($this->admin);

        // Mock the RouterBackupService
        $mockService = Mockery::mock(RouterBackupService::class);
        $mockService->shouldReceive('cleanupOldBackups')
            ->once()
            ->with(
                Mockery::on(fn ($r) => $r->id === $this->router->id),
                30 // Default retention
            )
            ->andReturn(3);

        $this->app->instance(RouterBackupService::class, $mockService);

        $response = $this->post(route('panel.admin.routers.backup.cleanup', $this->router->id), []);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'deleted_count' => 3,
        ]);
    }

    /**
     * Test create backup requires valid data.
     */
    public function test_create_backup_requires_valid_data(): void
    {
        $this->actingAs($this->admin);

        // Missing required 'name' field
        $response = $this->post(route('panel.admin.routers.backup.create', $this->router->id), [
            'reason' => 'Test reason',
        ]);

        $response->assertSessionHasErrors(['name']);

        // Name too long
        $response = $this->post(route('panel.admin.routers.backup.create', $this->router->id), [
            'name' => str_repeat('a', 300),
            'reason' => 'Test reason',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
