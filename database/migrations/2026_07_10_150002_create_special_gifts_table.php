<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ranked prizes for the Special Quiz, one row per rank per show. Mirrors
     * `live_show_winner_prizes` but adds an extensible `type`
     * (cash | voucher | custom) that drives which winner email is sent.
     */
    public function up(): void
    {
        Schema::create('special_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->onDelete('cascade');
            $table->unsignedTinyInteger('rank'); // 1 = 1st place, 2 = 2nd, etc.
            $table->string('name'); // gift name shown to the winner
            $table->string('type')->default('cash'); // cash | voucher | custom (extensible)
            $table->decimal('value', 8, 2)->nullable(); // cash payout / voucher value
            $table->decimal('voucher_amount', 8, 2)->nullable();
            $table->foreignId('discount_rule_id')->nullable()->constrained('shopify_price_rule')->nullOnDelete();
            $table->timestamps();

            $table->unique(['live_show_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_gifts');
    }
};
