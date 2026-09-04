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
        Schema::create('gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('prepaid_plans')->default(false);
            $table->boolean('subscription_plans')->default(false);
            $table->string('live_api_key')->nullable();
            $table->string('live_api_secret')->nullable();
            $table->string('sandbox_api_key')->nullable();
            $table->string('sandbox_api_secret')->nullable();
            $table->string('base_url')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->string('merchant_id')->nullable();
            $table->text('instructions')->nullable();
            $table->text('requisities')->nullable();
            $table->boolean('sandbox')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};
