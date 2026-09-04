<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Broadcast — per-user connected destinations.
 *
 * One row per connected target (a Telegram group, a Slack/Teams channel
 * webhook, a WhatsApp recipient list, an email list). Channel-specific
 * credentials live in the encrypted `credentials` JSON column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('channel_broadcast_destinations')) {
            return;
        }

        Schema::create('channel_broadcast_destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('channel', 30);     // whatsapp|telegram|slack|teams|email
            $table->string('name');            // user-given label
            $table->string('avatar', 1024)->nullable();

            // Encrypted channel credentials (chat_id, webhook_url, recipients, …)
            $table->text('credentials')->nullable();

            // Arbitrary metadata (resolved chat title, member count, …)
            $table->text('metadata')->nullable();

            $table->boolean('status')->default(true);          // active / paused
            $table->boolean('last_test_ok')->nullable();
            $table->timestamp('last_tested_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_broadcast_destinations');
    }
};
