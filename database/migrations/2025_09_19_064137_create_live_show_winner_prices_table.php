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
        Schema::create('live_show_winner_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->onDelete('cascade');
            $table->unsignedTinyInteger('rank'); // 1 = 1st place, 2 = 2nd, etc.
            $table->string('prize'); // e.g. 50.00 for 50%
            $table->timestamps();

            $table->unique(['live_show_id', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_show_winner_prizes');
    }
};
