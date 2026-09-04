<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V330 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('links')->where(['privacy' => 0])->update(['privacy' => 3]);
        DB::table('links')->where(['privacy' => 1])->update(['privacy' => 0]);
        DB::table('links')->where(['privacy' => 3])->update(['privacy' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
