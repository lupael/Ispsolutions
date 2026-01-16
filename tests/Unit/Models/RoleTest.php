<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_role_has_permission_method_works(): void
    {
        $role = Role::where('slug', 'admin')->first();

        $this->assertTrue($role->hasPermission('users.manage'));
        $this->assertFalse($role->hasPermission('nonexistent.permission'));
    }

    public function test_role_get_permissions_returns_array(): void
    {
        $role = Role::where('slug', 'admin')->first();

        $permissions = $role->getPermissions();

        $this->assertIsArray($permissions);
        $this->assertContains('users.manage', $permissions);
    }

    public function test_super_admin_has_wildcard_permission(): void
    {
        $role = Role::where('slug', 'super-admin')->first();

        $this->assertContains('*', $role->getPermissions());
    }

    public function test_all_9_roles_are_seeded(): void
    {
        $expectedRoles = [
            'super-admin',
            'admin',
            'manager',
            'staff',
            'reseller',
            'sub-reseller',
            'card-distributor',
            'customer',
            'developer',
        ];

        foreach ($expectedRoles as $slug) {
            $this->assertDatabaseHas('roles', ['slug' => $slug]);
        }

        $this->assertCount(9, Role::all());
    }

    public function test_roles_have_correct_hierarchy_levels(): void
    {
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $customer = Role::where('slug', 'customer')->first();

        $this->assertEquals(100, $superAdmin->level);
        $this->assertEquals(90, $admin->level);
        $this->assertEquals(10, $customer->level);
    }

    public function test_user_can_have_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'admin')->first();

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
    }
}
