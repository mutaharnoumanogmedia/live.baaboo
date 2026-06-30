<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            $table->foreignId('push_subscription_id')
                ->nullable()
                ->after('user_id')
                ->constrained('push_subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('push_subscription_id');
        });
    }
};
