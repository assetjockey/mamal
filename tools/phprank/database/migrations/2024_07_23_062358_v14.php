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
        DB::table('settings')->insert(
            [
                ['name' => 'request_http_version', 'value' => '1.1'],
            ]
        );

        DB::table('settings')->where('name', '=', 'request_user_agent')->update(['value' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
