<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Coupons plugin — schema
|--------------------------------------------------------------------------
|
| Adds the two tables the plugin needs plus a single JSON column on the
| orders table.
|
| Design note — coupons are PERCENTAGE-ONLY discounts applied at checkout to
| ONE-TIME plans (prepaid / lifetime). They never touch monthly/yearly
| subscriptions, whose price lives in gateway-side plan resources. A coupon
| lowers the amount the gateway charges (via the in-memory Plan price) and the
| resulting Order stores both the discounted price and a `coupon` JSON snapshot
| ({code, percentage, original_price, discount_amount}). The authoritative
| per-redemption record lives in `coupon_redemptions`.
|
| Every Schema::create is guarded with hasTable() so the migration is
| idempotent and safe to re-run when the plugin ships as an add-on.
|
*/
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name')->nullable();

                // Percentage discount applied to a one-time plan price (1–100).
                $table->unsignedTinyInteger('percentage')->default(0);

                // active | inactive — matches the project's string-status style.
                $table->string('status', 20)->default('active')->index();

                // Total number of times this coupon may be redeemed across all users.
                $table->unsignedInteger('max_redemptions')->default(1);
                // How many redemptions have happened so far.
                $table->unsignedInteger('redeemed_count')->default(0);
                // Max redemptions allowed for one individual user (business rule: 1).
                $table->unsignedInteger('per_user_limit')->default(1);

                // Sharing target. NULL = open/public code (any eligible user may
                // use it). Set = a coupon shared with one specific user, shown on
                // their profile and usable only by them.
                $table->unsignedBigInteger('owner_id')->nullable()->index();

                // Groups a bulk generation so batches can be filtered/exported.
                $table->string('batch_id', 40)->nullable()->index();

                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();

                $table->text('note')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('coupon_redemptions')) {
            Schema::create('coupon_redemptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->nullable()->index();
                $table->string('code')->index();
                $table->unsignedBigInteger('user_id')->index();
                // Percentage applied at the moment of redemption (snapshot).
                $table->unsignedTinyInteger('percentage')->default(0);
                // The order the coupon was applied to.
                $table->string('order_id', 50)->nullable()->index();
                // Money snapshot for reporting.
                $table->decimal('original_amount', 15, 2)->nullable();
                $table->decimal('discount_amount', 15, 2)->nullable();
                $table->char('currency', 3)->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
            });
        }

        // One-time discount snapshot on the order it was used for.
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'coupon')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('coupon')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'coupon')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('coupon');
            });
        }

        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
    }
};
