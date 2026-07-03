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
            $table->string('winner_email_sent_status')->nullable();
            $table->string('winner_voucher_email_sent_status')->nullable();
            $table->string('winner_cash_email_sent_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_live_shows', function (Blueprint $table) {
            //
            $table->dropColumn('winner_email_sent_status');
            $table->dropColumn('winner_voucher_email_sent_status');
            $table->dropColumn('winner_cash_email_sent_status');
        });
    }
};
