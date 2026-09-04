<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ad Performance Analytics — normalized daily metrics.
 *
 * Canonical, provider-agnostic daily rows. Every adapter maps its native
 * reporting shape onto these columns so the dashboards never branch per
 * provider. Grain: (ad_account_id, level, entity_id, date).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ad_metrics')) {
            return;
        }

        Schema::create('ad_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ad_account_id')->constrained('ad_accounts')->onDelete('cascade');

            $table->string('provider', 20);
            $table->string('level', 12);          // campaign|adset|ad
            $table->string('entity_id', 191);      // remote id of the campaign/adset/ad
            $table->string('entity_name')->nullable();
            $table->string('campaign_id', 191)->nullable();
            $table->string('campaign_name')->nullable();

            $table->date('date');

            // Canonical base metrics (spend/value stored in the account currency)
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('spend', 16, 4)->default(0);
            $table->decimal('conversions', 14, 2)->default(0);
            $table->decimal('conversion_value', 16, 4)->default(0);

            $table->string('currency', 10)->nullable();

            $table->timestamps();

            $table->unique(['ad_account_id', 'level', 'entity_id', 'date'], 'ad_metric_grain_unique');
            $table->index(['user_id', 'date']);
            $table->index(['ad_account_id', 'date']);
            $table->index(['ad_account_id', 'level', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_metrics');
    }
};
