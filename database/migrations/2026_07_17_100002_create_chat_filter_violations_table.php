<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// chat_filter_module: audit log of every filter hit. Doubles as the ban log
// (timestamps to point at if a user complains) and the Tier 4 watchlist feed.
return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_filter_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('live_show_id')->nullable();
            $table->unsignedBigInteger('chat_filter_word_id')->nullable();
            $table->unsignedTinyInteger('tier_number')->nullable();
            $table->string('matched_term')->nullable();
            $table->text('original_message');
            // chat_filter_module: what the service actually did with the message
            $table->enum('action_taken', ['deleted', 'timeout', 'banned', 'flagged'])->default('deleted');
            // chat_filter_module: mods tick this off when they have handled a watchlist entry
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'live_show_id']);
            $table->index(['tier_number', 'is_reviewed']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_filter_violations');
    }
};
