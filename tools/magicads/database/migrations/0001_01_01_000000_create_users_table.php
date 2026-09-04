<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('user_id')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable()->index();
            $table->string('password')->nullable();
            $table->string('status', 20)->nullable()->index();
            $table->string('group', 20)->default('user')->index();
            $table->string('workbook', 100)->nullable();
            $table->integer('plan_id')->nullable()->index();
            $table->string('company', 100)->nullable();
            $table->string('website')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->nullable();
            $table->decimal('balance', 12, 2)->default(0.00);           
            $table->decimal('wallet', 12, 2)->default(0.00);           
            $table->string('avatar')->nullable();
            $table->timestamp('last_seen')->nullable()->index();
            $table->string('google2fa_secret', 100)->nullable();
            $table->boolean('google2fa_enabled')->default(0);
            $table->string('referral_id', 20)->nullable();
            $table->string('referred_by', 20)->nullable();
            $table->string('referral_paypal', 191)->nullable();
            $table->text('referral_bank')->nullable();
            $table->boolean('personal_api_key')->default(false);
            $table->boolean('hidden_plan')->default(false);
            $table->boolean('used_free_tier')->default(false);
            $table->string('theme', 10)->default('light');
            $table->boolean('subscription_required')->default(false);
            $table->string('verification_code', 100)->nullable();
            $table->boolean('email_opt_in')->default(true);           
            $table->unsignedInteger('credits')->default(0);
            $table->unsignedInteger('credits_prepaid')->default(0);
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->unsignedTinyInteger('onboarding_current_step')->default(0);
            $table->unsignedTinyInteger('onboarding_total_steps')->default(15);
            $table->boolean('onboarding_skipped')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('provider', 30)->nullable();
            $table->string('provider_id')->nullable();
            $table->index(['provider', 'provider_id']);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['group', 'status']);
            $table->index(['plan_id', 'status']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
