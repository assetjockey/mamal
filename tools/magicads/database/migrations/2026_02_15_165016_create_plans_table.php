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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id', 20)->unique()->index();
            $table->string('name');
            $table->decimal('price', 15, 2)->unsigned();
            $table->char('currency', 3)->default('USD');
            $table->enum('status', ['active', 'closed', 'hidden'])->default('active');
            $table->enum('plan_type', ['prepaid', 'monthly', 'yearly', 'lifetime'])->nullable();
            $table->text('description')->nullable();
            $table->boolean('featured')->default(0);
            $table->boolean('free')->default(0);
            $table->text('features')->nullable();
            $table->integer('credits')->unsigned()->default(0);            
            $table->unsignedMediumInteger('projects')->default(0);
            $table->unsignedSmallInteger('order')->nullable();
            $table->unsignedSmallInteger('team_members')->default(0);
            $table->boolean('personal_api')->default(false);
            $table->unsignedSmallInteger('days')->nullable();
            $table->string('stripe_plan_id', 100)->nullable();
            $table->string('paypal_plan_id', 100)->nullable();
            $table->string('stripe_product_id', 100)->nullable();
            $table->string('paypal_product_id', 100)->nullable();
            $table->string('stripe_plan_fp', 64)->nullable();
            $table->string('paypal_plan_fp', 64)->nullable();
            $table->string('paystack_plan_id', 100)->nullable();
            $table->string('paystack_plan_fp', 64)->nullable();
            $table->string('razorpay_plan_id', 100)->nullable();
            $table->string('razorpay_plan_fp', 64)->nullable();
            $table->string('mollie_plan_id', 100)->nullable();
            $table->string('mollie_plan_fp', 64)->nullable();
            $table->string('flutterwave_plan_id', 100)->nullable();
            $table->string('flutterwave_plan_fp', 64)->nullable();
            $table->string('paddle_product_id', 100)->nullable();
            $table->string('paddle_plan_id', 100)->nullable();
            $table->string('paddle_plan_fp', 64)->nullable();
            $table->string('yookassa_plan_id', 100)->nullable();
            $table->string('yookassa_plan_fp', 64)->nullable();
            $table->string('mercadopago_plan_id', 100)->nullable();
            $table->string('mercadopago_plan_fp', 64)->nullable();
            $table->string('iyzico_product_id', 100)->nullable();
            $table->string('iyzico_plan_id', 100)->nullable();
            $table->string('iyzico_plan_fp', 64)->nullable();
            $table->string('midtrans_plan_id', 100)->nullable();
            $table->string('midtrans_plan_fp', 64)->nullable();
            $table->string('braintree_plan_id', 100)->nullable();
            $table->string('braintree_plan_fp', 64)->nullable();
            $table->boolean('image_studio_feature')->nullable()->default(1);
            $table->boolean('video_studio_feature')->nullable()->default(1);
            $table->boolean('copy_studio_feature')->nullable()->default(1); 
            $table->boolean('fashion_studio_feature')->nullable()->default(0); 
            $table->boolean('product_photoshoot_feature')->nullable()->default(0); 
            $table->boolean('avatar_video_feature')->nullable()->default(0); 
            $table->boolean('ugc_creator_feature')->nullable()->default(0); 
            $table->boolean('social_media_studio_feature')->nullable()->default(0);
            $table->boolean('channel_broadcast_feature')->nullable()->default(0);
            $table->timestamps();

            $table->index(['status', 'plan_type', 'order']);
            $table->index(['status', 'plan_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
