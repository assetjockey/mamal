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
        Schema::create('google_adsense', function (Blueprint $table) {
            $table->id();

            // Master switch + AdSense account.
            $table->boolean('enabled')->default(false);
            $table->string('publisher_id')->nullable(); // ca-pub-XXXXXXXXXXXXXXXX
            $table->boolean('auto_ads')->default(false);

            // Per-placement ad unit slot IDs. A placement renders on the
            // frontend only when its slot ID is filled.
            $table->string('slot_home_top')->nullable();
            $table->string('slot_home_bottom')->nullable();
            $table->string('slot_blog_top')->nullable();
            $table->string('slot_blog_article')->nullable();
            $table->string('slot_blog_bottom')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_adsense');
    }
};
