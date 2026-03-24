<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Current gallery overlay state per live show (for late joiners + video sync).
     *
     * - Image: only url + media_type=image; no second-based fields.
     * - Video: url + media_type=video + playback_started_at (sync) + optional video_duration_seconds (clamp).
     */
    public function up(): void
    {
        Schema::create('live_show_gallery_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->unique()->constrained('live_shows')->onDelete('cascade');

            $table->boolean('is_visible')->default(false);

            $table->foreignId('gallery_media_id')->nullable()->constrained('gallery_media')->nullOnDelete();
            $table->text('url')->nullable();
            $table->enum('media_type', ['image', 'video'])->nullable();

            /** Server anchor for video sync: late joiners seek to now() - playback_started_at */
            $table->timestamp('playback_started_at')->nullable();

            /** Known length of the video file in seconds (optional; use to clamp seek / UI) */
            $table->unsignedInteger('video_duration_seconds')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_show_gallery_states');
    }
};
