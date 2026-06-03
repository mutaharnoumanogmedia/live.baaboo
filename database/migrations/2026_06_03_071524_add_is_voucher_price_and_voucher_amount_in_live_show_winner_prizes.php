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
            $table->boolean('is_voucher_price')->default(false)->after('prize');
            $table->decimal('voucher_amount', 8, 2)->nullable()->after('is_voucher_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_show_winner_prizes', function (Blueprint $table) {
            $table->dropColumn('is_voucher_price');
            $table->dropColumn('voucher_amount');
        });
    }
};
