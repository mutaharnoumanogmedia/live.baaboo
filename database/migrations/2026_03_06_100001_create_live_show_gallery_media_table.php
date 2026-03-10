<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_show_gallery_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->onDelete('cascade');
            $table->foreignId('gallery_media_id')->constrained('gallery_media')->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['live_show_id', 'gallery_media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_show_gallery_media');
    }
};
