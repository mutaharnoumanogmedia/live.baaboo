<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Baseline data for the sequential E2E test suite.
 *
 * Seeds only roles, permissions and an admin account. All other data
 * (registered users, live shows, players, winners) is created by the
 * ordered E2E tests themselves and persists in database/testing.sqlite.
 */
class E2EBaselineSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@baaboo.test';

    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $admin = User::create([
            'name' => 'E2E Admin',
            'email' => self::ADMIN_EMAIL,
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
    }
}
