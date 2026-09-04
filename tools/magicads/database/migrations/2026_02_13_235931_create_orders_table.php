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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('frequency', 20)->nullable()->comment('prepaid|monthly|yearly|lifetime');
            $table->string('order_id', 50)->index();
            $table->unsignedBigInteger('plan_id')->index();
            $table->decimal('price', 10, 2);
            $table->string('currency', 10);
            $table->string('type', 50)->nullable();
            $table->string('gateway_transaction_id', 100)->nullable();
            $table->string('gateway', 50);
            $table->string('status', 20)->comment('completed|cancelled|declined|failed|pending')->index();
            $table->string('plan_name', 100)->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->string('invoice')->nullable();
            $table->string('billing_first_name', 100)->nullable();
            $table->string('billing_last_name', 100)->nullable();
            $table->string('billing_email', 150)->nullable();
            $table->string('billing_phone', 30)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_vat_number', 50)->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_company', 160)->nullable();
            $table->unsignedSmallInteger('credits')->default(0);
            $table->string('payment_proof_path')->nullable();
            $table->dateTime('payment_proof_uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
