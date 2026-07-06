<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Media attached *before* a specific quiz question within a live show.
     *
     * This is kept separate from the show-level `live_show_gallery_media`
     * pivot so that existing full-show attachments keep working exactly as
     * before. A row here means: "show this media right before question X".
     */
    public function up(): void
    {
        Schema::create('live_show_question_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('live_show_quizzes')->onDelete('cascade');
            $table->foreignId('gallery_media_id')->constrained('gallery_media')->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['quiz_id', 'gallery_media_id']);
            $table->index('live_show_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_show_question_media');
    }
};
