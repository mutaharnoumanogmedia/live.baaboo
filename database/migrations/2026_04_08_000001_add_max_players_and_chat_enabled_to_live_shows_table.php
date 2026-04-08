<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('live_shows')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            if (! Schema::hasColumn('live_shows', 'max_players')) {
                $table->unsignedInteger('max_players')->default(300)->after('max_winners');
            }
            if (! Schema::hasColumn('live_shows', 'chat_enabled')) {
                $table->boolean('chat_enabled')->default(true)->after('max_players');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('live_shows')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            if (Schema::hasColumn('live_shows', 'chat_enabled')) {
                $table->dropColumn('chat_enabled');
            }
            if (Schema::hasColumn('live_shows', 'max_players')) {
                $table->dropColumn('max_players');
            }
        });
    }
};
