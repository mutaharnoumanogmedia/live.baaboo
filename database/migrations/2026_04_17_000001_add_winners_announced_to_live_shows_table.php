<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('live_shows')) {
            return;
        }
        if (Schema::hasColumn('live_shows', 'winners_announced')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            $table->boolean('winners_announced')->default(false);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('live_shows') || ! Schema::hasColumn('live_shows', 'winners_announced')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            $table->dropColumn('winners_announced');
        });
    }
};
