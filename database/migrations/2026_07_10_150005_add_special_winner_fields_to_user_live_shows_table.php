<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user Special Quiz winner outcome, stored alongside the existing main
     * winner columns on the pivot. These are independent of the main
     * is_winner / winner_prize_id fields so announcing special winners never
     * touches the main winners.
     */
    public function up(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->boolean('is_special_winner')->default(false)->after('is_winner');
            $table->foreignId('special_gift_id')->nullable()->after('is_special_winner')->constrained('special_gifts')->nullOnDelete();
            $table->string('special_prize_won')->nullable()->after('special_gift_id');
            $table->string('special_discount_code')->nullable()->after('special_prize_won');

            $table->string('special_winner_email_sent_status')->nullable();
            $table->timestamp('special_winner_email_sent_at')->nullable();
            $table->string('special_type_email_sent_status')->nullable();
            $table->timestamp('special_type_email_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            $table->dropForeign(['special_gift_id']);
            $table->dropColumn([
                'is_special_winner',
                'special_gift_id',
                'special_prize_won',
                'special_discount_code',
                'special_winner_email_sent_status',
                'special_winner_email_sent_at',
                'special_type_email_sent_status',
                'special_type_email_sent_at',
            ]);
        });
    }
};
