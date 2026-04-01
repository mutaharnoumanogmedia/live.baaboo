<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('live_shows') || Schema::hasColumn('live_shows', 'max_winners')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_winners')->default(3)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('live_shows') || ! Schema::hasColumn('live_shows', 'max_winners')) {
            return;
        }

        Schema::table('live_shows', function (Blueprint $table) {
            $table->dropColumn('max_winners');
        });
    }
};
