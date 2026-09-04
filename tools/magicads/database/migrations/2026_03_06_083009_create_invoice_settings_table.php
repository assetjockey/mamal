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
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
