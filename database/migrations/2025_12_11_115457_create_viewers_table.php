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
        Schema::create('viewers', function (Blueprint $table) {
            $table->id();
            $table->string("ip")->nullable();
            $table->longText("user_agent")->nullable();
            $table->string("location")->nullable();
            $table->longText("session_id")->nullable();
            $table->time("created_at")->nullable()->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->time("updated_at")->nullable()->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viewers');
    }
};
