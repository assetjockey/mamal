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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 20)->unique()->index();
            $table->string('user_id', 20)->index();
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('gateway', 50)->nullable();
            $table->enum('status', ['pending', 'approved', 'declined', 'paid'])->default('pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
