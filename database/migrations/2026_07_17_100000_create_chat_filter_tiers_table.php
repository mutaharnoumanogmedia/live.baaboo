<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// chat_filter_module: tiers hold the policy severity levels and the action to
// take when a word belonging to the tier matches a chat message.
return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_filter_tiers', function (Blueprint $table) {
            $table->id();
            // chat_filter_module: 1 = hard block/ban, 2 = vulgar, 3 = insults, 4 = spam/scam watchlist
            $table->unsignedTinyInteger('tier_number')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // chat_filter_module: default enforcement action for the whole tier
            $table->enum('action', ['ban', 'timeout', 'watchlist', 'hard_block'])->default('timeout');
            // chat_filter_module: whether the offending message is deleted / not broadcast
            $table->boolean('delete_message')->default(true);
            // chat_filter_module: master switch to take a tier live or offline
            $table->boolean('is_enabled')->default(true);
            // chat_filter_module: mute duration applied on repeat offences
            $table->unsignedInteger('timeout_minutes')->default(10);
            // chat_filter_module: how many violations before a timeout kicks in
            $table->unsignedInteger('timeout_after_offenses')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_filter_tiers');
    }
};
