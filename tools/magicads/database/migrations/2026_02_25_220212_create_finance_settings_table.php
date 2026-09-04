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
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->char('currency', 3)->default('USD');
            $table->unsignedSmallInteger('tax')->nullable();
            $table->boolean('billing_info')->default(false);
            $table->boolean('decimal_points')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_settings');
    }
};
