<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Permissions derived from admin sidebar items.
     * Run: php artisan db:seed --class=PermissionSeeder
     */
    public function run(): void
    {
        $permissions = [
            'can-manage-dashboard',
            'can-manage-live-shows',
            'can-manage-quiz-questions',
            'can-manage-media-gallery',
            'can-manage-players',
            'can-manage-analytics',
            'can-manage-settings',
            'can-manage-gtm',
            'can-manage-push-notifications',
            'can-manage-users',
            'can-manage-roles',
            'can-manage-permissions',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}
