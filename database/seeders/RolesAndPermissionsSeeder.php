<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view dashboard', 'manage accounts', 'manage users', 'manage roles',
            'manage products', 'manage landing pages', 'manage forms',
            'manage orders', 'view analytics', 'manage settings',
            'manage order statuses', 'manage customers',
        ];
        $permissionModels = collect($permissions)->map(
            fn (string $permission): Permission => Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]),
        );
        Role::findOrCreate('Super Admin', 'web')->syncPermissions($permissionModels);
        Role::findOrCreate('Account Owner', 'web')->syncPermissions($permissionModels->whereIn('name', [
            'view dashboard', 'manage users', 'manage products', 'manage landing pages',
            'manage forms', 'manage orders', 'view analytics', 'manage settings',
        ]));
        Role::findOrCreate('Viewer', 'web')->syncPermissions($permissionModels->whereIn('name', [
            'view dashboard', 'view analytics',
        ]));
    }
}
