<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Merge the "media before a question" concept into the show-level
     * `live_show_gallery_media` pivot.
     *
     * A nullable `before_question` column now holds the quiz id a media item
     * should play before. When it is null the row behaves exactly like a
     * normal show-wide attachment; when it points at a quiz the row means
     * "show this media right before question X". This replaces the separate
     * `live_show_question_media` table.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('live_show_gallery_media', 'before_question')) {
            Schema::table('live_show_gallery_media', function (Blueprint $table) {
                $table->foreignId('before_question')
                    ->nullable()
                    ->after('gallery_media_id')
                    ->constrained('live_show_quizzes')
                    ->cascadeOnDelete();
            });
        }

        // The same media can now be attached both show-wide (before_question
        // null) and before one or more questions, so the uniqueness has to
        // include the question column. Create the new composite unique first
        // so the `live_show_id` foreign key keeps a backing index when the old
        // unique is dropped.
        Schema::table('live_show_gallery_media', function (Blueprint $table) {
            $table->unique(['live_show_id', 'gallery_media_id', 'before_question'], 'lsgm_show_media_question_unique');
        });

        Schema::table('live_show_gallery_media', function (Blueprint $table) {
            $table->dropUnique('live_show_gallery_media_live_show_id_gallery_media_id_unique');
        });

        // Carry any existing question attachments over to the merged table.
        if (Schema::hasTable('live_show_question_media')) {
            $rows = DB::table('live_show_question_media')->get();
            foreach ($rows as $row) {
                DB::table('live_show_gallery_media')->updateOrInsert(
                    [
                        'live_show_id' => $row->live_show_id,
                        'gallery_media_id' => $row->gallery_media_id,
                        'before_question' => $row->quiz_id,
                    ],
                    [
                        'sort_order' => $row->sort_order ?? 0,
                        'media_played' => $row->media_played ?? false,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Restore the original unique first so the `live_show_id` foreign key
        // keeps a backing index when the composite unique is dropped.
        Schema::table('live_show_gallery_media', function (Blueprint $table) {
            $table->unique(['live_show_id', 'gallery_media_id']);
        });

        Schema::table('live_show_gallery_media', function (Blueprint $table) {
            $table->dropUnique('lsgm_show_media_question_unique');
            $table->dropForeign(['before_question']);
            $table->dropColumn('before_question');
        });
    }
};
