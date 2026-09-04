<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('options')) {
            return;
        }

        $defaults = [
            'website_favicon' => 'public/img/favicon.png',
            'website_logo_dark' => 'public/img/logo-dark.png',
            'website_logo_light' => 'public/img/logo-light.png',
            'website_logo_brand_dark' => 'public/img/logo-brand-dark.png',
            'website_logo_brand_light' => 'public/img/logo-brand-light.png',
        ];

        foreach ($defaults as $key => $value) {
            $current = DB::table('options')->where('name', $key)->value('value');

            if (is_string($current) && trim($current) !== '' && ! str_contains($current, 'stackposts')) {
                continue;
            }

            DB::table('options')->updateOrInsert(
                ['name' => $key],
                ['value' => $value]
            );

            Cache::forget("options.{$key}");
            Cache::forget("options.v2.{$key}");
        }
    }

    public function down(): void
    {
        //
    }
};
