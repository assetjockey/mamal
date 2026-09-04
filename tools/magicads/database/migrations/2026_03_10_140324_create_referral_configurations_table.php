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
        Schema::create('referral_configurations', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->boolean('enabled')->default(false);
            $table->string('payment_policy', 10)->default('first')->comment('all, first');
            $table->decimal('payment_commission', 5, 2)->default(10);
            $table->decimal('payment_threshold', 8, 2)->default(50);
            $table->unsignedInteger('payment_credits')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_configurations');
    }
};
