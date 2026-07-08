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
        // The "ON UPDATE CURRENT_TIMESTAMP" clause is MySQL-specific and is not
        // understood by SQLite (used by the test suite), so it is only applied on
        // MySQL/MariaDB. Other drivers fall back to a plain CURRENT_TIMESTAMP
        // default, which is enough for the schema to build during testing.
        $isMysql = in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);

        Schema::create('viewers', function (Blueprint $table) use ($isMysql) {
            $table->id();
            $table->string("ip")->nullable();
            $table->longText("user_agent")->nullable();
            $table->string("location")->nullable();
            $table->longText("session_id")->nullable();
            $table->timestamp("created_at")->nullable()->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp("updated_at")->nullable()->default(\DB::raw(
                $isMysql ? 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP' : 'CURRENT_TIMESTAMP'
            ));
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
