<?php

use App\Models\Extension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Broadcast — dedicated settings table + plugin registration.
 *
 * Single-row, mirrors the Social Media Studio settings pattern. Holds:
 *   - feature flags (master switch + free-tier access)
 *   - the AI message pricing override (JSON)
 *   - per-channel enable switches
 *   - global channel credentials (encrypted via the model)
 *
 * Also registers the marketplace `extensions` row as installed so
 * HelperService::extensionCheckChannelBroadcast() can gate visibility — same
 * self-contained install approach the Team plugin uses.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('channel_broadcast_settings')) {
            Schema::create('channel_broadcast_settings', function (Blueprint $table) {
                $table->id();

                // Feature gating
                $table->boolean('channel_broadcast_feature')->default(false);
                $table->boolean('channel_broadcast_free_tier')->default(false);

                // AI message pricing override (credits per 1000 words) — JSON
                $table->text('ai_pricing')->nullable();

                // Per-channel enable switches
                $table->boolean('whatsapp_enabled')->default(false);
                $table->boolean('telegram_enabled')->default(false);
                $table->boolean('slack_enabled')->default(false);
                $table->boolean('email_enabled')->default(false);
                $table->boolean('messenger_enabled')->default(false);


                $table->timestamps();
            });

            // Seed the single settings row so ::first() is always present.
            DB::table('channel_broadcast_settings')->insert([
                'channel_broadcast_feature'   => false,
                'channel_broadcast_free_tier' => false,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('channel_broadcast_settings');
    }
};
