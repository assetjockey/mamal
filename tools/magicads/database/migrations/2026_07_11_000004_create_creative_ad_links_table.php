<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ad Performance Analytics — creative <-> external ad attribution bridge.
 *
 * Maps a MagicAds ad_creatives row to an external ad id so creative-level ROAS
 * can be computed. One external ad maps to one creative per ad account.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('creative_ad_links')) {
            return;
        }

        Schema::create('creative_ad_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ad_account_id')->constrained('ad_accounts')->onDelete('cascade');
            $table->foreignId('creative_id')->constrained('ad_creatives')->onDelete('cascade');

            $table->string('provider', 20);
            $table->string('external_ad_id', 191);
            $table->string('external_ad_name')->nullable();
            $table->string('method', 12)->default('manual'); // tag|manual|auto
            $table->unsignedTinyInteger('confidence')->default(100); // 0-100

            $table->timestamps();

            $table->unique(['ad_account_id', 'external_ad_id'], 'creative_link_ad_unique');
            $table->index(['user_id', 'creative_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creative_ad_links');
    }
};
