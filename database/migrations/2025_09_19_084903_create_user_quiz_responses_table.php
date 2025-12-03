<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_quiz_id')->constrained('user_quizzes')->onDelete('cascade');
            $table->foreignId('quiz_option_id')->constrained('quiz_options')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('live_show_quizzes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_correct')->default(false);
            $table->integer('seconds_to_submit')->default(0)->nullable();
            $table->text('user_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_quiz_responses');
    }
};
