<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Special Quiz configuration/progress on the show itself. `special_max_winners`
     * defaults to 0, meaning a show has no special winners until configured, so
     * existing shows are unaffected.
     */
    public function up(): void
    {
        Schema::table('live_shows', function (Blueprint $table) {
            if (! Schema::hasColumn('live_shows', 'special_max_winners')) {
                $table->unsignedTinyInteger('special_max_winners')->default(0)->after('max_winners');
            }
            if (! Schema::hasColumn('live_shows', 'special_winners_announced')) {
                $table->boolean('special_winners_announced')->default(false)->after('winners_announced');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_shows', function (Blueprint $table) {
            $table->dropColumn('special_max_winners');
            $table->dropColumn('special_winners_announced');
        });
    }
};
