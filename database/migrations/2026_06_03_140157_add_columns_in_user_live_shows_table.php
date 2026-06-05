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
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->unsignedBigInteger("winner_prize_id")->nullable()->after("is_winner");
            $table->foreign("winner_prize_id")->references("id")->on("live_show_winner_prizes")->onDelete("set null");
            $table->string("discount_code")->nullable()->after("winner_prize_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->dropForeign(['winner_prize_id']);
            $table->dropColumn('winner_prize_id');
            $table->dropColumn('discount_code');
        });
    }
};
