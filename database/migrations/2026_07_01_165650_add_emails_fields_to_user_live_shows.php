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
            //
            $table->timestamp('winner_cash_email_sent_at')->nullable();
            $table->timestamp('winner_voucher_email_sent_at')->nullable();
            $table->timestamp('winner_email_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            //
            $table->dropColumn('winner_cash_email_sent_at');
            $table->dropColumn('winner_voucher_email_sent_at');
            $table->dropColumn('winner_email_sent_at');
        });
    }
};
