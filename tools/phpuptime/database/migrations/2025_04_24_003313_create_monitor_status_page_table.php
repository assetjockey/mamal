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
        Schema::create('monitor_status_page', function (Blueprint $table) {
            $table->integer('status_page_id')->index('status_page_id');
            $table->integer('monitor_id')->index('monitor_id');
            $table->integer('order')->nullable();
            $table->unique(['status_page_id', 'monitor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_status_page');
    }
};
