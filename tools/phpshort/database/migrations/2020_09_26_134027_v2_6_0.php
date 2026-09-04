<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V260 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert(
            [
                [
                    'name' => 'paginate',
                    'value' => '10'
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
