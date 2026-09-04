<?php

namespace Database\Seeders;

use App\Models\FeatureSetting;
use Illuminate\Database\Seeder;

class FeatureSettingSeeder extends Seeder
{
    /**
     * Ensure a default feature_settings row exists.
     *
     * The create-table migration defines ->default(1) on every studio column,
     * but those defaults only apply when a row is inserted. On a fresh install
     * the table is empty, so HelperService::studioAccess()/studioFeatureEnabled()
     * see FeatureSetting::first() === null and disable Copy/Image/Video studios
     * even though the feature and free-tier flags are meant to be on by default.
     *
     * This seeder inserts that first row when the table is empty. It is
     * idempotent and never modifies an existing row, so it is safe to run on
     * sites where the admin has already saved their AI settings.
     */
    public function run(): void
    {
        if (FeatureSetting::query()->exists()) {
            return;
        }

        FeatureSetting::create([
            'image_studio_feature'   => 1,
            'video_studio_feature'   => 1,
            'copy_studio_feature'    => 1,
            'image_studio_free_tier' => 1,
            'video_studio_free_tier' => 1,
            'copy_studio_free_tier'  => 1,
        ]);
    }
}
