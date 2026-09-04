<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Gift Cards plugin — schema
|--------------------------------------------------------------------------
|
| Adds the three tables the plugin needs plus a single display-only counter
| column on the users table.
|
| Design note — gift cards grant usage CREDITS, never wallet money. `wallet`
| is the withdrawable pool (marketplace earnings, referral commissions);
| crediting gift value there would let users cash it out, which is illogical.
| Redeemed value is added to `users.credits_prepaid` — spendable on generations
| but never withdrawable — so we reuse an existing column and don't touch the
| credit billing mechanism. The authoritative per-redemption record lives in
| `gift_card_redemptions`.
|
| Every Schema::create is guarded with hasTable() so the migration is
| idempotent and safe to re-run when the plugin ships as an add-on.
|
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gift_cards')) {
            Schema::create('gift_cards', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name')->nullable();

                // Usage credits granted to the redeemer (added to users.credits).
                $table->unsignedInteger('credits')->default(0);

                // active | inactive — matches the project's string-status style.
                $table->string('status', 20)->default('active')->index();

                // Total number of times this single card may be redeemed.
                $table->unsignedInteger('max_redemptions')->default(1);
                // How many redemptions have happened so far.
                $table->unsignedInteger('redeemed_count')->default(0);
                // Max redemptions allowed for one individual user on this card.
                $table->unsignedInteger('per_user_limit')->default(1);

                // Current holder. NULL = open/public campaign code (anyone may
                // redeem). Set = personal card that only the owner can redeem
                // and can be transferred to another user.
                $table->unsignedBigInteger('owner_id')->nullable()->index();

                // Groups a bulk generation so batches can be filtered/exported.
                $table->string('batch_id', 40)->nullable()->index();

                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();

                $table->text('note')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('gift_card_redemptions')) {
            Schema::create('gift_card_redemptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('gift_card_id')->index();
                $table->string('code')->index();
                $table->unsignedBigInteger('user_id')->index();
                // Credits granted at the moment of redemption (snapshot).
                $table->unsignedInteger('credits')->default(0);
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('gift_card_transfers')) {
            Schema::create('gift_card_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_id', 40)->unique();
                $table->unsignedBigInteger('gift_card_id')->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->unsignedBigInteger('receiver_id')->index();
                $table->timestamps();
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_transfers');
        Schema::dropIfExists('gift_card_redemptions');
        Schema::dropIfExists('gift_cards');
    }
};
