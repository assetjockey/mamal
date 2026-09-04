<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V120 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->smallInteger('default_domain')->after('role')->nullable();
            $table->smallInteger('default_space')->after('default_domain')->nullable();
            $table->smallInteger('default_stats')->after('default_space')->nullable();
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
