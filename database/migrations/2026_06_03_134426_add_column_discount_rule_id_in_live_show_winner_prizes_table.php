<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('live_show_winner_prizes', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_rule_id')->nullable()->after('voucher_amount');
            $table->foreign('discount_rule_id')->references('id')->on('shopify_price_rule')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_show_winner_prizes', function (Blueprint $table) {
            $table->dropForeign(['discount_rule_id']);
            $table->dropColumn('discount_rule_id');
        });
    }
};
