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
        Schema::create('live_show_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_show_id')->constrained('live_shows')->onDelete('no action');
            $table->unsignedBigInteger('user_id')->constrained('users')->onDelete('no action');
            $table->text('message');
            $table->boolean('is_removed')->default(false);
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
        Schema::dropIfExists('live_show_messages');
    }
};
