<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'view users']);

        Permission::create(['name' => 'create subscriptions']);
        Permission::create(['name' => 'edit subscriptions']);
        Permission::create(['name' => 'delete subscriptions']);
        Permission::create(['name' => 'view subscriptions']);

        // create roles and assign created permissions

        $role = Role::create(['name' => 'developer']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo([
            'create users',
            'edit users',
            'delete users',
            'view users',
            'create subscriptions',
            'edit subscriptions',
            'delete subscriptions',
            'view subscriptions',
        ]);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([
            'create users',
            'edit users',
            'delete users',
            'view users',
        ]);
    }
}
