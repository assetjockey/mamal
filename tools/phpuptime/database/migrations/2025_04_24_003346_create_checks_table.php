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
        Schema::create('checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('monitor_id')->index('monitor_id');
            $table->unsignedInteger('response_time')->default(0);
            $table->unsignedSmallInteger('response_status_code')->default(0);
            $table->string('country', 64)->nullable();
            $table->string('city', 128)->nullable();
            $table->timestamp('checked_at')->useCurrent()->index('checked_at');

            $table->index(['monitor_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checks');
    }
};
