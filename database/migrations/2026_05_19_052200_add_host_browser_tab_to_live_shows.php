<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds `host_browser_tab` to the `live_shows` table.
     *
     * This column stores the unique identifier of the browser tab that is
     * currently authorised to broadcast for this live show. Only one tab
     * (across any browser / device) may "own" the broadcast at a time: the
     * most recent tab to claim it wins, and the value stored here is the
     * source of truth used to kick out any older tabs.
     *
     * Stored as a nullable string so the column is empty until a tab claims
     * the broadcaster page for the first time.
     */
    public function up(): void
    {
        Schema::table('live_shows', function (Blueprint $table) {
            $table->string('host_browser_tab')->nullable()->default(null)->after('media_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_shows', function (Blueprint $table) {
            $table->dropColumn('host_browser_tab');
        });
    }
};
