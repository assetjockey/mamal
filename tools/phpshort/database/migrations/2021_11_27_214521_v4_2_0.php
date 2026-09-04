<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V420 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->index('slug', 'slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
