<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinanceSetting;

class FinanceSettingSeeder extends Seeder
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
            FinanceSetting::updateOrCreate(['id' => $ad['id']], $ad);
        }
    }
}
