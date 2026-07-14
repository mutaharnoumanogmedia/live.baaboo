<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('live_show_quizzes', function (Blueprint $table) {
            $table->unsignedInteger('sorting_order')->default(0)->after('question');
        });

        $liveShowIds = DB::table('live_show_quizzes')
            ->distinct()
            ->pluck('live_show_id');

        foreach ($liveShowIds as $liveShowId) {
            $quizzes = DB::table('live_show_quizzes')
                ->where('live_show_id', $liveShowId)
                ->orderBy('id')
                ->pluck('id');

            foreach ($quizzes as $index => $quizId) {
                DB::table('live_show_quizzes')
                    ->where('id', $quizId)
                    ->update(['sorting_order' => $index]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_show_quizzes', function (Blueprint $table) {
            $table->dropColumn('sorting_order');
        });
    }
};
