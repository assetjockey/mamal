<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds multi-vendor + per-resolution support to media_models.
 *
 * Some video engines (Seedance) can be served by more than one API vendor
 * with completely different request/response shapes — ByteDance's direct
 * API, fal.ai and kie.ai — while remaining a single model in the studio.
 * Exactly one vendor is active at a time.
 *
 *  - api_provider      the currently-selected vendor key for this model
 *                      (e.g. 'bytedance' | 'fal' | 'kie'). Null for ordinary
 *                      single-vendor models, which keep using `key_field`.
 *  - provider_settings map of {vendor => {label, key_field, model_id}} listing
 *                      every vendor that can power this model and the per-vendor
 *                      admin_keys column + concrete model id to use.
 *  - resolutions       map of {tier => {enabled, credit_cost}} for the quality
 *                      tiers the model exposes (480p / 720p / 1080p / 4k).
 *                      credit_cost is credits-per-second at that tier.
 *
 * The Seedance row is backfilled here so existing installs pick up the new
 * behaviour without a destructive re-seed (which would reset admin-tuned
 * pricing / toggles on every other model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_models', function (Blueprint $table) {
            if (! Schema::hasColumn('media_models', 'api_provider')) {
                // Active vendor for multi-vendor models. Null = single-vendor.
                $table->string('api_provider')->nullable();
            }
            if (! Schema::hasColumn('media_models', 'provider_settings')) {
                // JSON: available vendors + their per-vendor key_field/model_id.
                $table->text('provider_settings')->nullable();
            }
            if (! Schema::hasColumn('media_models', 'resolutions')) {
                // JSON: per-quality-tier enabled flag + per-second credit cost.
                $table->text('resolutions')->nullable();
            }
        });

        $this->backfillSeedance();
    }

    /**
     * Populate the Seedance row's multi-vendor config on existing installs
     * without touching any other row or column.
     */
    protected function backfillSeedance(): void
    {
        $seedance = DB::table('media_models')->where('vendor', 'seedance')->first();

        if (! $seedance) {
            return;
        }

        $update = [];

        if (empty($seedance->api_provider)) {
            // fal.ai is the vendor that was already implemented, so keep it as
            // the default active provider to preserve current behaviour.
            $update['api_provider'] = 'fal';
        }

        if (empty($seedance->provider_settings)) {
            $update['provider_settings'] = json_encode([
                'bytedance' => [
                    'label'     => 'ByteDance (Direct)',
                    'key_field' => 'seedance_key',
                    'model_id'  => 'doubao-seedance-2-0-260128',
                ],
                'fal' => [
                    'label'     => 'fal.ai',
                    'key_field' => 'fal_key',
                    'model_id'  => 'bytedance/seedance-2.0',
                ],
                'kie' => [
                    'label'     => 'kie.ai',
                    'key_field' => 'kie_key',
                    'model_id'  => 'bytedance/seedance-2',
                ],
            ]);
        }

        if (empty($seedance->resolutions)) {
            // credit_cost is credits per second at that tier. Seed sensible
            // defaults scaling with resolution; 4k off by default since only
            // some vendors (kie.ai) can actually produce it.
            $update['resolutions'] = json_encode([
                '480p'  => ['enabled' => true,  'credit_cost' => 2],
                '720p'  => ['enabled' => true,  'credit_cost' => 4],
                '1080p' => ['enabled' => true,  'credit_cost' => 7],
                '4k'    => ['enabled' => false, 'credit_cost' => 14],
            ]);
        }

        if ($update !== []) {
            DB::table('media_models')->where('vendor', 'seedance')->update($update);
        }
    }

    public function down(): void
    {
        Schema::table('media_models', function (Blueprint $table) {
            foreach (['resolutions', 'provider_settings', 'api_provider'] as $col) {
                if (Schema::hasColumn('media_models', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
