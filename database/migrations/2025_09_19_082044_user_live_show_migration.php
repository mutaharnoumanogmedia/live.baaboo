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
        //
        Schema::create('user_live_shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('no action');
            $table->foreignId('live_show_id')->constrained('live_shows')->onDelete('no action');
            $table->float('score')->default(0)->nullable();
            $table->float('prize_won')->unsigned()->default(0);

            $table->string('status')->nullable()->default('registered');
            $table->boolean('is_winner')->default(false);
            $table->timestamp('last_active')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
