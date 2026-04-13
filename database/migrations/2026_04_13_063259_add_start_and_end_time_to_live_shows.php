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
        Schema::table('live_shows', function (Blueprint $table) {
            //
            $table->dateTime('start_time')->nullable()->after('scheduled_at');
            $table->dateTime('end_time')->nullable()->after('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_shows', function (Blueprint $table) {
            //
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
    }
};
