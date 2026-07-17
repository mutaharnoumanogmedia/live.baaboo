<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// chat_filter_module: global temporary mute (timeout) applied to a user across
// all shows, mirroring the existing permanent global ban.
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('chat_muted_until')->nullable()->after('blocked_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('chat_muted_until');
        });
    }
};
