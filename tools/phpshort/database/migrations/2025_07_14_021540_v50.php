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
        DB::table('settings')->insert([
            ['name' => 'custom_server_addr', 'value' => '']
        ]);

        foreach (DB::table('plans')->select('*')->cursor() as $row) {
            $features = json_decode($row->features, true);

            $features['link_alias'] = 1;

            DB::statement("UPDATE `plans` SET `features` = :features WHERE `id` = :id", ['features' => json_encode($features), 'id' => $row->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
