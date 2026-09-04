<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ad Performance Analytics — cached AI performance analyses.
 *
 * Generating an insight spends credits, so each analysis is persisted and
 * re-served. `metrics_snapshot` records the KPI totals the analysis was based
 * on; `recommendations` holds the structured action list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ad_insights')) {
            return;
        }

        Schema::create('ad_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('range_from');
            $table->date('range_to');
            $table->string('provider', 20)->nullable(); // null = all providers

            $table->longText('summary')->nullable();
            $table->text('recommendations')->nullable();  // JSON array
            $table->text('metrics_snapshot')->nullable();  // JSON KPI totals

            $table->unsignedInteger('credits_spent')->default(0);

            $table->string('headline')->nullable();
            $table->string('overall_health', 20)->nullable();
            $table->text('wins')->nullable();       
            $table->text('risks')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_insights');
    }
};
