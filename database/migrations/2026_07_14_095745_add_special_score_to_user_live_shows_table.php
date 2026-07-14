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
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->float('special_score')->default(0)->nullable()->after('score');
        });

        DB::table('user_live_shows')->select('id', 'user_id', 'live_show_id')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $sum = DB::table('user_special_quiz_responses')
                    ->join('live_show_quizzes', 'user_special_quiz_responses.quiz_id', '=', 'live_show_quizzes.id')
                    ->where('user_special_quiz_responses.user_id', $row->user_id)
                    ->where('live_show_quizzes.live_show_id', $row->live_show_id)
                    ->sum('user_special_quiz_responses.response_score');

                DB::table('user_live_shows')
                    ->where('id', $row->id)
                    ->update(['special_score' => $sum ?? 0]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->dropColumn('special_score');
        });
    }
};
