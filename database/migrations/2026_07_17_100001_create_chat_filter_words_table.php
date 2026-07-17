<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// chat_filter_module: the actual dictionary of blocked/watched terms. Each row
// belongs to a tier but may override the tier action (e.g. link spam living in
// the Tier 4 watchlist still hard-blocks).
return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_filter_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_filter_tier_id')->constrained('chat_filter_tiers')->cascadeOnDelete();
            $table->string('term');
            // chat_filter_module: literal = single word, phrase = multi-word substring, regex = raw pattern
            $table->enum('match_type', ['literal', 'phrase', 'regex'])->default('literal');
            // chat_filter_module: require \b word boundaries to avoid false positives (neger -> Nigeria)
            $table->boolean('whole_word')->default(false);
            // chat_filter_module: per-word live switch (evasion/regex groups shipped disabled)
            $table->boolean('is_active')->default(true);
            // chat_filter_module: optional per-word action that beats the tier action
            $table->enum('action_override', ['ban', 'timeout', 'watchlist', 'hard_block'])->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['term', 'match_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_filter_words');
    }
};
