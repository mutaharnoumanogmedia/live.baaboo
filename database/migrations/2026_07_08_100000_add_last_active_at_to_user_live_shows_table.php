<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The gameplay join flow (GamePlayController::registerUser,
 * HomeController::liveShowMagicLink) writes a `last_active_at` value onto the
 * user_live_shows pivot when attaching/updating players, but the original pivot
 * migration only defined `last_active`. This adds the missing column so the
 * schema matches what the application code actually persists.
 *
 * The hasColumn guard keeps it safe on any environment where the column was
 * already added manually.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_live_shows', 'last_active_at')) {
            Schema::table('user_live_shows', function (Blueprint $table) {
                $table->timestamp('last_active_at')->nullable()->after('last_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_live_shows', 'last_active_at')) {
            Schema::table('user_live_shows', function (Blueprint $table) {
                $table->dropColumn('last_active_at');
            });
        }
    }
};
