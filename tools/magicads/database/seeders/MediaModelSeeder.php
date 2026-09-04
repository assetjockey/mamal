<?php

namespace Database\Seeders;

use App\Models\MediaModel;
use Illuminate\Database\Seeder;

/**
 * Seeds the media_models table with the default AI engine registry —
 * the same set previously held in config('ai-studio.providers').
 *
 * Idempotent: uses updateOrCreate keyed on `vendor`, so re-running won't
 * duplicate rows and will refresh defaults on existing installs without
 * clobbering admin-toggled `is_active` / `recommended` flags only if you
 * choose to preserve them (see note below — by default this resets all
 * managed fields to the canonical defaults).
 */
class MediaModelSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->models() as $model) {
            MediaModel::updateOrCreate(
                ['vendor' => $model['vendor']],
                $model,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function models(): array
    {
        return [
            // ============================================================
            // IMAGE PROVIDERS
            // ============================================================
            [
                'vendor' => 'gemini',
                'label' => 'Google Nano Banana 2',
                'sub_label' => 'Gemini 3.1 Flash Image · 4K',
                'model_id' => 'gemini-3.1-flash-image',
                'type' => 'image',
                'driver' => \App\Services\AiStudio\Drivers\GeminiDriver::class,
                'key_field' => 'gemini_key',
                'description' => 'Best overall realism with character consistency, native 4K output, and strong editing. Default for ads with photoreal subjects or multilingual text.',
                'tags' => ['Top realism', '4K', 'Editing', 'Multilingual text'],
                'tier' => 'premium',
                'audio' => false,
                'durations' => null,
                'max_duration' => null,
                'credit_cost' => 2,
                'text_rendering' => 'best',
                'max_resolution' => 4096,
                'recommended' => true,
                'is_active' => true,
                'sort_order' => 10,
                'icon_svg' => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
            ],
            [
                'vendor' => 'openai',
                'label' => 'OpenAI GPT Image 2',
                'sub_label' => 'gpt-image-2 · 99% text accuracy',
                'model_id' => 'gpt-image-2',
                'type' => 'image',
                'driver' => \App\Services\AiStudio\Drivers\GptImageDriver::class,
                'key_field' => 'openai_key',
                'description' => 'Quality-first model with the most reliable English text rendering and high-fidelity edits. Best for portrait photoreal and English headline ads.',
                'tags' => ['English text', 'Photoreal', 'Editing', 'Up to 4K'],
                'tier' => 'premium',
                'audio' => false,
                'durations' => null,
                'max_duration' => null,
                'credit_cost' => 2,
                'text_rendering' => 'best',
                'image_quality' => 'medium',
                'max_resolution' => 4096,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 20,
                'icon_svg' => '<path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"/><path d="M9 13a4.5 4.5 0 0 0 3-4"/><path d="M6.003 5.125A3 3 0 0 0 6.401 6.5"/><path d="M3.477 10.896a4 4 0 0 1 .585-.396"/><path d="M6 18a4 4 0 0 1-1.967-.516"/><path d="M12 13h4"/><path d="M12 18h6a2 2 0 0 1 2 2v1"/><path d="M12 8h8"/><path d="M16 8V5a2 2 0 0 1 2-2"/><circle cx="16" cy="13" r=".5"/><circle cx="18" cy="3" r=".5"/><circle cx="20" cy="21" r=".5"/><circle cx="20" cy="8" r=".5"/>',
            ],
            [
                'vendor' => 'flux',
                'label' => 'FLUX.2 Pro',
                'sub_label' => 'Black Forest Labs · Multi-reference',
                'model_id' => 'flux-2-pro',
                'type' => 'image',
                'driver' => \App\Services\AiStudio\Drivers\FluxDriver::class,
                'key_field' => 'flux_key',
                'description' => 'Multi-reference conditioning (up to 8 source images) for character + brand consistency, photoreal at 4MP, deterministic outputs. Replaces Stable Diffusion as the open-weight-adjacent workhorse.',
                'tags' => ['Multi-reference', 'Photoreal', '4MP', 'Brand consistency'],
                'tier' => 'mid',
                'audio' => false,
                'durations' => null,
                'max_duration' => null,
                'credit_cost' => 1,
                'text_rendering' => 'good',
                'max_resolution' => 2048,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 30,
                'icon_svg' => '<path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"/>',
            ],
            [
                'vendor' => 'ideogram',
                'label' => 'Ideogram 3.0',
                'sub_label' => 'Typography specialist',
                'model_id' => 'ideogram-v3',
                'type' => 'image',
                'driver' => \App\Services\AiStudio\Drivers\IdeogramDriver::class,
                'key_field' => 'ideogram_key',
                'description' => 'The undisputed leader for in-image text. ~90% text accuracy on legibility benchmarks. Best for posters, sale graphics, infographics, and ads with bold headlines.',
                'tags' => ['Typography', 'Posters', 'Sale graphics', 'Headlines'],
                'tier' => 'mid',
                'audio' => false,
                'durations' => null,
                'max_duration' => null,
                'credit_cost' => 1,
                'text_rendering' => 'best',
                'max_resolution' => 2048,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 40,
                'icon_svg' => '<polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/>',
            ],
            [
                'vendor' => 'recraft',
                'label' => 'Recraft V3',
                'sub_label' => 'Vector + brand asset specialist',
                'model_id' => 'recraft-v3',
                'type' => 'image',
                'driver' => \App\Services\AiStudio\Drivers\RecraftDriver::class,
                'key_field' => 'recraft_key',
                'description' => 'Trained on logo, icon and poster data with native vector (SVG) output and brand-style consistency. Best for flat design, logo concepts and icon work.',
                'tags' => ['Vectors', 'Logos', 'Flat design', 'Brand assets'],
                'tier' => 'mid',
                'audio' => false,
                'durations' => null,
                'max_duration' => null,
                'credit_cost' => 1,
                'text_rendering' => 'good',
                'max_resolution' => 2048,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 50,
                'icon_svg' => '<path d="M8.3 10a.7.7 0 0 1-.626-1.079L11.4 3a.7.7 0 0 1 1.198-.043L16.3 8.9a.7.7 0 0 1-.572 1.1Z"/><rect x="3" y="14" width="7" height="7" rx="1"/><circle cx="17.5" cy="17.5" r="3.5"/>',
            ],

            // ============================================================
            // VIDEO PROVIDERS
            // ============================================================
            [
                'vendor' => 'veo',
                'label' => 'Google Veo 3.1',
                'sub_label' => 'Gemini API · Native audio',
                'model_id' => 'veo-3.1-generate-preview',
                'type' => 'video',
                'driver' => \App\Services\AiStudio\Drivers\VeoDriver::class,
                'key_field' => 'gemini_key',
                'description' => 'Film-grade 1080p with native synchronised audio, dialogue and ambience in a single pass. Best default for ads with on-camera speech.',
                'tags' => ['Native audio', 'Dialogue', '1080p', 'I2V'],
                'tier' => 'premium',
                'audio' => true,
                'durations' => [4, 6, 8],
                'max_duration' => 8,
                'credit_cost' => 12,
                'text_rendering' => 'native',
                'max_resolution' => null,
                'recommended' => true,
                'is_active' => true,
                'sort_order' => 60,
                'icon_svg' => '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>',
            ],
            [
                'vendor' => 'runway',
                'label' => 'Runway Gen-4 Turbo',
                'sub_label' => 'Runway · Consistency & VFX',
                'model_id' => 'gen4_turbo',
                'type' => 'video',
                'driver' => \App\Services\AiStudio\Drivers\RunwayDriver::class,
                'key_field' => 'runway_key',
                'description' => 'Strongest character + product consistency across multiple shots, with the best VFX/editing toolset. Best for UGC, handheld and brand films.',
                'tags' => ['Consistency', 'UGC', 'Cinematic', 'I2V'],
                'tier' => 'premium',
                'audio' => false,
                'durations' => [5, 10],
                'max_duration' => 10,
                'credit_cost' => 10,
                'text_rendering' => 'weak',
                'max_resolution' => null,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 70,
                'icon_svg' => '<path d="M20.2 6 3 11l-.9-2.4c-.3-1.1.3-2.2 1.3-2.5l13.5-4c1.1-.3 2.2.3 2.5 1.3Z"/><path d="m6.2 5.3 3.1 3.9"/><path d="m12.4 3.4 3.1 4"/><path d="M3 11h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>',
            ],
            [
                'vendor' => 'kling',
                'label' => 'Kling 3.0 Pro',
                'sub_label' => 'Kuaishou · Native audio',
                'model_id' => 'fal-ai/kling-video/v3/pro',
                'type' => 'video',
                'driver' => \App\Services\AiStudio\Drivers\KlingDriver::class,
                // Multi-vendor: the active vendor's key column is resolved from
                // provider_settings; key_field is the fal.ai fallback.
                'key_field' => 'fal_key',
                'api_provider' => 'fal',
                'provider_settings' => [
                    'klingai' => [
                        'label'     => 'Kling AI (Direct)',
                        'key_field' => 'kling_key',
                        'model_id'  => 'kling-v3',
                    ],
                    'fal' => [
                        'label'     => 'fal.ai',
                        'model_id'  => 'fal-ai/kling-video/v3',
                        'key_field' => 'fal_key',
                    ],
                    'kie' => [
                        'label'     => 'kie.ai',
                        'key_field' => 'kie_key',
                        'model_id'  => 'kling-3.0/video',
                    ],
                ],
                // Per-second credit cost per quality tier. Kling exposes
                // 720p / 1080p / 4k (no 480p). 4k off by default.
                'resolutions' => [
                    '720p'  => ['enabled' => true,  'credit_cost' => 6],
                    '1080p' => ['enabled' => true,  'credit_cost' => 10],
                    '4k'    => ['enabled' => false, 'credit_cost' => 20],
                ],
                'description' => 'Cinematic-quality leader, powered by your choice of the Kling AI API, fal.ai or kie.ai. Best at human motion, orbit/360° product shots and complex camera moves, with native audio and multi-shot support. Clips from 3s up to 15s.',
                'tags' => ['Orbit', 'Human motion', 'Native audio', 'Multi-shot'],
                'tier' => 'premium',
                'audio' => true,
                'durations' => [4, 5, 6, 8, 10, 12, 15],
                'max_duration' => 15,
                'credit_cost' => 10,
                'text_rendering' => 'weak',
                'max_resolution' => null,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 80,
                'icon_svg' => '<circle cx="12" cy="12" r="3"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><path d="M10.4 21.9a10 10 0 0 0 9.941-15.416"/><path d="M13.5 2.1a10 10 0 0 0-9.841 15.416"/>',
            ],
            [
                'vendor' => 'seedance',
                'label' => 'Seedance 2.0',
                'sub_label' => 'ByteDance · Native audio',
                'model_id' => 'bytedance/seedance-2.0',
                'type' => 'video',
                'driver' => \App\Services\AiStudio\Drivers\SeedanceDriver::class,
                // Multi-vendor: the active vendor's key column is resolved from
                // provider_settings; key_field is the fal.ai fallback.
                'key_field' => 'fal_key',
                'api_provider' => 'fal',
                'provider_settings' => [
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
                ],
                // Per-second credit cost per quality tier. 4k off by default
                // (only some vendors — e.g. kie.ai — can produce it).
                'resolutions' => [
                    '480p'  => ['enabled' => true,  'credit_cost' => 2],
                    '720p'  => ['enabled' => true,  'credit_cost' => 4],
                    '1080p' => ['enabled' => true,  'credit_cost' => 7],
                    '4k'    => ['enabled' => false, 'credit_cost' => 14],
                ],
                'description' => 'ByteDance\'s second-generation video model, powered by your choice of the ByteDance API, fal.ai or kie.ai. Native synchronized audio, multi-shot editing and director-level camera control. Great for high-energy promos and dynamic, motion-graphic-leaning content.',
                'tags' => ['Native audio', 'Motion graphics', 'Energetic', 'Multi-shot'],
                'tier' => 'budget',
                'audio' => true,
                'durations' => [4, 5, 6, 8, 10, 12, 15],
                'max_duration' => 15,
                'credit_cost' => 4,
                'text_rendering' => 'weak',
                'max_resolution' => null,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 90,
                'icon_svg' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
            ],
            [
                'vendor' => 'veo-lite',
                'label' => 'Veo 3.1 Lite',
                'sub_label' => 'Gemini API · Cheap iterations',
                'model_id' => 'veo-3.1-fast-generate-preview',
                'type' => 'video',
                'driver' => \App\Services\AiStudio\Drivers\VeoDriver::class,
                'key_field' => 'gemini_key',
                'description' => 'Faster, cheaper Veo tier for quick iterations and atmospheric clips. Same engine, lower fidelity + shorter generation time.',
                'tags' => ['Fast', 'Native audio', 'Iteration', 'Budget'],
                'tier' => 'budget',
                'audio' => true,
                'durations' => [4, 6, 8],
                'max_duration' => 8,
                'credit_cost' => 6,
                'text_rendering' => 'native',
                'max_resolution' => null,
                'recommended' => false,
                'is_active' => true,
                'sort_order' => 100,
                'icon_svg' => '<path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>',
            ],
        ];
    }
}
