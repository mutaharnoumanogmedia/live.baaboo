<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Answers to Special Quiz questions. Kept in a dedicated table so the main
     * score accessor (which sums `user_quiz_responses`) never sees them, giving
     * automatic isolation between the main and special rankings.
     */
    public function up(): void
    {
        Schema::create('user_special_quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('live_show_quizzes')->onDelete('no action');
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('quiz_option_id')->nullable()->constrained('quiz_options')->onDelete('no action');
            $table->boolean('is_correct')->default(false);
            $table->float('seconds_to_submit')->default(0.0)->nullable();
            $table->float('response_score')->default(0.00)->nullable();
            $table->text('user_response')->nullable();
            $table->timestamps();

            // One answer per user per special question (prevents duplicates).
            $table->unique(['user_id', 'quiz_id'], 'usqr_user_quiz_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_special_quiz_responses');
    }
};
