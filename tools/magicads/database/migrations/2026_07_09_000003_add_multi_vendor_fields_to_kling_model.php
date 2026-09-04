<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Turns the Kling video engine into a multi-vendor model, mirroring Seedance.
 *
 * Kling can be powered by one of three interchangeable API vendors:
 *   - klingai : Kling AI's own API (Kuaishou, https://kling.ai) — the original
 *               first-party vendor. Key: admin_keys.kling_key (JWT "ak:sk").
 *   - fal     : fal.ai's hosted queue route. Key: admin_keys.fal_key.
 *   - kie     : kie.ai's unified Market jobs API. Key: admin_keys.kie_key.
 *
 * The multi-vendor columns (api_provider / provider_settings / resolutions)
 * were already added to media_models by the Seedance migration; this only
 * backfills the Kling row so existing installs pick up the behaviour without a
 * destructive re-seed. Kling exposes 720p / 1080p / 4k tiers (no 480p).
 */
return new class extends Migration
{
    public function up(): void
    {
        $kling = DB::table('media_models')->where('vendor', 'kling')->first();

        if (! $kling) {
            return;
        }

        $update = [];

        if (empty($kling->api_provider)) {
            // fal.ai was the vendor already implemented — keep it active so
            // current behaviour is preserved.
            $update['api_provider'] = 'fal';
        }

        if (empty($kling->provider_settings)) {
            $update['provider_settings'] = json_encode([
                'klingai' => [
                    'label'     => 'Kling AI (Direct)',
                    'key_field' => 'kling_key',
                    'model_id'  => 'kling-v3',
                ],
                'fal' => [
                    'label'     => 'fal.ai',
                    'key_field' => 'fal_key',
                    // Base slug; the fal client appends the quality segment
                    // (/standard, /pro, or none for 4k) + the endpoint.
                    'model_id'  => 'fal-ai/kling-video/v3',
                ],
                'kie' => [
                    'label'     => 'kie.ai',
                    'key_field' => 'kie_key',
                    'model_id'  => 'kling-3.0/video',
                ],
            ]);
        }

        if (empty($kling->resolutions)) {
            // credit_cost is credits per second at that tier. Kling is a
            // premium engine, so tiers are pricier than Seedance. 4k off by
            // default (heavier + not every vendor path is guaranteed).
            $update['resolutions'] = json_encode([
                '720p'  => ['enabled' => true,  'credit_cost' => 6],
                '1080p' => ['enabled' => true,  'credit_cost' => 10],
                '4k'    => ['enabled' => false, 'credit_cost' => 20],
            ]);
        }

        if ($update !== []) {
            DB::table('media_models')->where('vendor', 'kling')->update($update);
        }
    }

    public function down(): void
    {
        // Non-destructive: leave the shared multi-vendor columns in place
        // (they are owned by the Seedance migration). Just detach Kling's
        // vendor config so it reverts to a single-vendor fal.ai engine.
        DB::table('media_models')->where('vendor', 'kling')->update([
            'api_provider'      => null,
            'provider_settings' => null,
            'resolutions'       => null,
        ]);
    }
};
