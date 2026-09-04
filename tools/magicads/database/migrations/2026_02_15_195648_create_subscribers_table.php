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
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->dateTime('active_until')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['active', 'deactive', 'pending', 'cancelled', 'ended'])->default('active');
            $table->string('gateway', 30)->nullable();
            $table->decimal('amount_due', 15, 2)->unsigned();
            $table->char('currency', 3)->default('USD');
            $table->enum('plan_type', ['prepaid', 'monthly', 'yearly', 'lifetime'])->nullable();
            $table->string('subscription_id', 30);            
            $table->string('external_subscription_id')->nullable();            
            $table->unsignedInteger('credits')->default(0);
            $table->text('gateway_data')->nullable();           
            $table->timestamps();

            $table->index(['user_id', 'status', 'active_until']);
            $table->index(['status', 'active_until']);
            $table->index(['status', 'plan_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
