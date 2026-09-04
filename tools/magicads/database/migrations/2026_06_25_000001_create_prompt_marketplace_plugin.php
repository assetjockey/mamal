<?php

use App\Models\Extension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prompt Marketplace plugin — schema + install registration.
 *
 * Ships with the "magicads-prompt-marketplace" plugin. Mirrors the
 * self-contained Team / Avatar Studio install pattern: a single migration that
 *
 *   1. Creates `prompt_listings` — a seller's offer for a single generated
 *      creative. Carries a stable `public_id` (used by checkout so pricing is
 *      always pulled live from the DB), the snapshotted prompt + media preview,
 *      the free/paid flag and the seller-editable price.
 *   2. Creates `prompt_purchases` — the PERSISTENT record of a sale. It fully
 *      snapshots the prompt and a *copied* preview file, so a buyer keeps their
 *      purchase even if the seller later deletes the original creative or
 *      unlists it. This table doubles as the admin transaction ledger.
 *   3. Adds the plugin's settings columns to the shared single-row
 *      extension_settings table (master switch + optional platform commission).
 *   4. Registers the marketplace `extensions` row as installed so
 *      HelperService::extensionPromptMarketplace() can gate visibility.
 *
 * It deliberately touches NOTHING owned by the SaaS billing plugin.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Listings (one active offer per generated creative) ----
        if (! Schema::hasTable('prompt_listings')) {
            Schema::create('prompt_listings', function (Blueprint $table) {
                $table->id();

                // Stable, opaque id used in URLs / checkout so the price is
                // always re-read live from this row at purchase time.
                $table->uuid('public_id')->unique();

                // Link back to the source creative. Nulled (not cascaded) on
                // delete so the listing can be gracefully removed by the
                // AdCreativeObserver while keeping its history intact.
                $table->foreignId('ad_creative_id')->nullable()
                    ->constrained('ad_creatives')->nullOnDelete();

                $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();

                $table->enum('type', ['image', 'video']);
                $table->string('title', 160);
                // Public-facing marketing blurb — NEVER the protected prompt.
                $table->string('description', 500)->nullable();

                // The protected asset — snapshotted from the creative at publish
                // time. Hidden for paid listings until a buyer pays.
                $table->longText('prompt');
                $table->text('generation_meta')->nullable();

                // Media preview snapshot (so the card renders independently).
                $table->string('preview_path')->nullable();
                $table->string('preview_disk', 20)->nullable()->default('local');
                $table->string('mime_type', 50)->nullable();
                $table->unsignedSmallInteger('width')->nullable();
                $table->unsignedSmallInteger('height')->nullable();
                $table->unsignedTinyInteger('duration')->nullable();

                // Pricing — seller editable at any time.
                $table->boolean('is_paid')->default(true);
                $table->decimal('price', 10, 2)->default(0);
                $table->string('currency', 3)->default('USD');

                // active | unlisted | removed
                $table->string('status', 20)->default('active');

                // Lightweight denormalised counters for fast dashboards.
                $table->unsignedInteger('sales_count')->default(0);
                $table->decimal('revenue_total', 12, 2)->default(0);
                $table->unsignedInteger('views')->default(0);

                $table->timestamps();

                $table->index(['status', 'type', 'is_paid']);
                $table->index(['seller_id', 'status']);
                // A creative can have at most one live listing row.
                $table->unique('ad_creative_id');
            });
        }

        // ---- Purchases (persistent ledger — survives listing/creative removal) ----
        if (! Schema::hasTable('prompt_purchases')) {
            Schema::create('prompt_purchases', function (Blueprint $table) {
                $table->id();
                $table->uuid('public_id')->unique();

                // Soft references — the buyer keeps the snapshot even when the
                // listing or seller account disappears.
                $table->foreignId('listing_id')->nullable()
                    ->constrained('prompt_listings')->nullOnDelete();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('seller_id')->nullable()
                    ->constrained('users')->nullOnDelete();

                $table->enum('type', ['image', 'video']);
                $table->string('title', 160);

                // The reason the buyer paid — snapshotted forever.
                $table->longText('prompt');
                $table->text('generation_meta')->nullable();

                // A COPY of the preview media, persisted independently of the
                // seller's original so it never breaks when the source is deleted.
                $table->string('preview_path')->nullable();
                $table->string('preview_disk', 20)->nullable()->default('local');
                $table->string('mime_type', 50)->nullable();
                $table->unsignedSmallInteger('width')->nullable();
                $table->unsignedSmallInteger('height')->nullable();
                $table->unsignedTinyInteger('duration')->nullable();

                // Money breakdown for the transaction ledger.
                $table->decimal('price_paid', 10, 2)->default(0);
                $table->decimal('commission_amount', 10, 2)->default(0);
                $table->decimal('seller_earning', 10, 2)->default(0);
                $table->string('currency', 3)->default('USD');

                $table->string('gateway', 30)->default('wallet');
                // Idempotency key (gateway txn id / wallet ref). Stops a
                // webhook/return double-firing from settling a sale twice.
                $table->string('order_reference')->unique();
                // completed | pending | refunded
                $table->string('status', 20)->default('completed');

                $table->timestamps();

                $table->index(['buyer_id', 'status']);
                $table->index(['seller_id', 'status']);
                $table->index(['status', 'created_at']);
            });
        }

        // ---- extension_settings columns ----
        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'prompt_marketplace_feature')) {
                $table->boolean('prompt_marketplace_feature')->default(false);
            }
            // Optional platform fee (percent, 0–100). Default 0 → the seller
            // receives the EXACT amount paid, as specified.
            if (! Schema::hasColumn('extension_settings', 'prompt_marketplace_commission')) {
                $table->unsignedTinyInteger('prompt_marketplace_commission')->default(0);
            }
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_purchases');
        Schema::dropIfExists('prompt_listings');

        Schema::table('extension_settings', function (Blueprint $table) {
            foreach (['prompt_marketplace_feature', 'prompt_marketplace_commission'] as $column) {
                if (Schema::hasColumn('extension_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

    }
};
