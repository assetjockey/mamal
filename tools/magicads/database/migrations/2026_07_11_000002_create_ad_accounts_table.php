<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ad Performance Analytics — per-user connected ad accounts.
 *
 * One row per authorized ad account on an external network. Access/refresh
 * tokens are encrypted at rest via the model's `encrypted` casts.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ad_accounts')) {
            return;
        }

        Schema::create('ad_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('provider', 20);      // meta|google|tiktok
            $table->string('external_id', 191);   // act_<id> | customer id | advertiser_id
            $table->string('name')->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('timezone', 64)->nullable();

            // Tokens (encrypted via model casts)
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('access_token_expires_at')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();

            // Provider metadata (business id, manager id, scopes, …)
            $table->text('metadata')->nullable();

            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status', 20)->nullable(); // ok|error|syncing
            $table->text('last_sync_error')->nullable();

            $table->boolean('status')->default(true); // active / paused
            $table->timestamps();

            $table->unique(['user_id', 'provider', 'external_id'], 'ad_account_unique');
            $table->index(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_accounts');
    }
};
