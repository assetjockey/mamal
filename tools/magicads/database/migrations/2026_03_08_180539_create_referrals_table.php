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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referrer_id', 20)->nullable()->index();
            $table->string('referrer_email', 191)->nullable();
            $table->string('referred_id', 20)->nullable()->index();
            $table->string('referred_email', 191)->nullable();
            $table->unsignedTinyInteger('rate')->nullable();
            $table->string('order_id', 100)->nullable()->index();
            $table->decimal('payment', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->nullable()->index();
            $table->string('gateway', 50)->nullable();
            $table->timestamp('order_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
