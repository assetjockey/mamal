<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminKey;

class AdminKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ads = [
            ['id' => 1],

        ];

        foreach ($ads as $ad) {
            AdminKey::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
