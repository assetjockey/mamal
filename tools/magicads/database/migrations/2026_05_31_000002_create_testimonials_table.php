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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->longText('testimonial');
            $table->unsignedTinyInteger('stars')->default(5);
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->string('role')->nullable();
            $table->string('company')->nullable();
            $table->boolean('featured')->default(false);
            $table->string('status')->default('active');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
