<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prompt Marketplace — wallet withdrawals.
 *
 * Sellers earn into `users.wallet`. This adds the manual cash-out flow
 * (mirroring the SaaS affiliate payout UX, but kept entirely separate so the
 * two balance calculations never collide):
 *
 *   1. `prompt_withdrawals` — a seller's request to cash out part of their
 *      wallet. Funds are HELD at request time (the wallet is decremented and
 *      the amount parked on the row), then an admin settles it manually and
 *      marks it paid — or rejects it, which refunds the wallet.
 *   2. `extension_settings.prompt_marketplace_min_withdrawal` — the admin's
 *      minimum payout threshold.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('prompt_withdrawals')) {
            Schema::create('prompt_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('request_id', 32)->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('USD');

                // paypal | bank
                $table->string('method', 20)->default('paypal');
                // Snapshot of the payout destination the user supplied at request
                // time (PayPal email or bank requisites).
                $table->text('destination');

                // pending | approved | paid | rejected
                $table->string('status', 20)->default('pending');
                $table->string('admin_note', 500)->nullable();
                $table->timestamp('processed_at')->nullable();

                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['status', 'created_at']);
            });
        }

        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'prompt_marketplace_min_withdrawal')) {
                $table->decimal('prompt_marketplace_min_withdrawal', 8, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_withdrawals');

        Schema::table('extension_settings', function (Blueprint $table) {
            if (Schema::hasColumn('extension_settings', 'prompt_marketplace_min_withdrawal')) {
                $table->dropColumn('prompt_marketplace_min_withdrawal');
            }
        });
    }
};
