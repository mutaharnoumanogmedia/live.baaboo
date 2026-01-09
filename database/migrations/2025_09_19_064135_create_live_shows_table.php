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
        Schema::create('live_shows', function (Blueprint $table) {
            $table->id();
            $table->string('stream_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->longText('stream_link')->nullable();
            $table->enum('status', ['scheduled', 'live', 'completed'])->default('scheduled');

            $table->foreignId('created_by')->constrained('users')->onDelete('no action');
            $table->string('host_name')->nullable();

            $table->float('prize_amount')->default(0);
            $table->string('currency')->default('EUR');

            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();

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
        Schema::dropIfExists('live_shows');
    }
};
