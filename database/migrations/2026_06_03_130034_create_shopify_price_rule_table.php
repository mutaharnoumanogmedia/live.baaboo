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
        Schema::create('shopify_price_rule', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_id')->unique(); // Shopify PriceRule ID (numeric or gid)
            $table->string('title');
            $table->string('type')->default('percentage'); // percentage / fixed / shipping
            $table->decimal('value', 8, 2);
            $table->text('collection_ids')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true); // local status toggle
            $table->json('conditions')->nullable(); // JSON field for conditions
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_price_rule');
    }
};
