<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mark a quiz question as belonging to the Special Quiz. Existing rows
     * default to false (main quiz) so current shows behave exactly as before.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('live_show_quizzes', 'is_special')) {
            Schema::table('live_show_quizzes', function (Blueprint $table) {
                $table->boolean('is_special')->default(false)->after('question');
                $table->index('is_special');
            });
        }
    }

    public function down(): void
    {
        Schema::table('live_show_quizzes', function (Blueprint $table) {
            $table->dropIndex(['is_special']);
            $table->dropColumn('is_special');
        });
    }
};
