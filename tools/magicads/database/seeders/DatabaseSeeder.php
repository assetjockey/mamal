<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $this->call([
            RolesSeeder::class,
            GatewaysSeeder::class,
            MediaModelSeeder::class,
            TextModelSeeder::class,
            AdminKeySeeder::class,
            FinanceSettingSeeder::class,
            FeatureSettingSeeder::class,
            PromptSeeder::class
        ]);
    }
}
