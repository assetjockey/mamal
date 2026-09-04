<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->where('name', '=', 'short_protocol')->update(['name' => 'domain_protocol']);
        DB::table('settings')->where('name', '=', 'short_splash_redirect_seconds')->update(['name' => 'short_splash_redirect_delay_seconds']);

        DB::table('settings')->insert([
            ['name' => 'favicon_driver', 'value' => 'duckduckgo'],
            ['name' => 'stripe_tax', 'value' => '0'],
            ['name' => 'storage_signed_urls', 'value' => '0'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
