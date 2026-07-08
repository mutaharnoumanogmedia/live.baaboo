<?php

namespace Tests\E2E;

use Database\Seeders\E2EBaselineSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Base case for the sequential E2E suite.
 *
 * - Uses database/testing.sqlite (file persists across all E2E classes).
 * - migrate:fresh + E2EBaselineSeeder run exactly once per suite execution.
 * - No RefreshDatabase: later tests depend on data written by earlier ones.
 */
abstract class E2ETestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->useE2EDatabase();

        parent::setUp();

        if (! E2EContext::$migratedAndSeeded) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->seed(E2EBaselineSeeder::class);

            E2EContext::$adminUserId = \App\Models\User::where('email', E2EBaselineSeeder::ADMIN_EMAIL)
                ->value('id');

            E2EContext::$migratedAndSeeded = true;
        }
    }

    /**
     * Point the sqlite connection at database/testing.sqlite before Laravel boots.
     */
    private function useE2EDatabase(): void
    {
        $path = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'testing.sqlite';

        if (! file_exists($path)) {
            touch($path);
        }

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE='.$path);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $path;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $path;
    }
}
