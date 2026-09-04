<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdCreative extends Model
{
    protected $guarded = [];

    protected $casts = [
        'brand_kit_snapshot' => 'array',
        'generation_meta' => 'array',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'last_polled_at' => 'datetime',
        'poll_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Public URL for the generated asset.
     *
     * Files generated after the move to the `results` disk are stored at
     * `public/results/{images|videos}/{provider}/{uuid}.{ext}` and served as
     * `/results/...`.
     *
     * Legacy paths still beginning with `ai-studio/` were stored on the
     * `local`/`public` disk pre-migration; we keep resolving those via
     * `Storage::url()` so older items in the gallery continue to load.
     */
    public function fileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        // Offloaded to a cloud storage provider — resolve the URL through the
        // StorageManager. Falls through to local if the provider is gone
        // (plugin uninstalled) or can't produce a URL.
        if ($this->storage_disk && $this->storage_disk !== 'local') {
            $provider = app(\App\Services\Storage\StorageManager::class)->provider($this->storage_disk);

            if ($provider) {
                $url = $provider->url($this->file_path);

                if ($url) {
                    return $url;
                }
            }
        }

        if (str_starts_with($this->file_path, 'ai-studio/')) {
            return Storage::url($this->file_path);
        }

        return Storage::disk('results')->url($this->file_path);
    }

    /**
     * Break the fully-assembled generation prompt back into its parts for a
     * clean gallery display:
     *   - description    → the free-text the user actually typed
     *   - attributes     → the structured selections (industry, tone, …)
     *   - brand_context  → the brand block, if one was folded in
     *
     * The wizard assembles prompts as
     *   "Industry: x. Tone: y. {user text} Color scheme: z.\nCanvas: …\nBrand context: …"
     * so we strip the known labelled segments and whatever survives is the
     * user's own description. Works for both image and video prompts, and for
     * older rows written before generation_meta carried structured fields.
     */
    public function promptBreakdown(): array
    {
        // Prefer the structured brief persisted on generation_meta (written by
        // the studio since the prompt-quality upgrade). It is the exact set of
        // user inputs, so there is no need to regex-parse the assembled prompt.
        $meta = $this->generation_meta;
        if (is_array($meta) && ! empty($meta['brief']) && is_array($meta['brief'])) {
            return $this->breakdownFromBrief($meta['brief'], $meta);
        }

        $raw = (string) $this->prompt;

        // Canvas + Brand context are appended on their own lines by PromptBuilder.
        $brandContext = null;
        if (preg_match('/^Brand context:\s*(.+)$/mi', $raw, $m)) {
            $brandContext = trim($m[1]);
        }

        // Drop the trailing Canvas / Brand context lines from the working copy.
        $working = preg_replace('/\n?(Canvas|Brand context):.*/is', '', $raw);

        // Labelled segments produced by the studio wizards. Order doesn't
        // matter — we pull each out wherever it sits and collect what's left.
        $patterns = [
            'Industry'      => '/Industry:\s*([^.]+)\./i',
            'Objective'     => '/Ad objective:\s*([^.]+)\./i',
            'Tone'          => '/Tone:\s*([^.]+)\./i',
            'Visual Style'  => '/Visual style:\s*([^.]+)\./i',
            'Platform'      => '/Platform:\s*([^.]+?)\s*ad\./i',
            'Goal'          => '/Goal:\s*([^.]+)\./i',
            'Camera/Motion' => '/Camera\/motion:\s*([^.]+)\./i',
            'Mood'          => '/Mood:\s*([^.]+)\./i',
            'Color Scheme'  => '/Color scheme:\s*([^.]+)\./i',
            // headline / CTA are also surfaced from generation_meta, but parse
            // them here too so they don't leak into the description text.
            'Headline'      => '/Include headline text:\s*"([^"]*)"\./i',
            'CTA'           => '/Include call-to-action button:\s*"([^"]*)"\./i',
            'Overlay Text'  => '/Include text overlay:\s*"([^"]*)"\./i',
            'CTA End'       => '/End with call-to-action:\s*"([^"]*)"\./i',
        ];

        $attributes = [];
        foreach ($patterns as $label => $pattern) {
            if (preg_match($pattern, $working, $m)) {
                $value = trim($m[1]);
                if ($value !== '') {
                    // Normalise slugs like "product_launch" → "product launch".
                    $attributes[] = [
                        'label' => $label === 'CTA End' ? 'CTA' : $label,
                        'value' => str_replace('_', ' ', $value),
                    ];
                }
                $working = preg_replace($pattern, '', $working, 1);
            }
        }

        // Whatever remains, with double-spaces collapsed, is the user's text.
        $description = trim(preg_replace('/\s{2,}/', ' ', $working));

        return [
            'description'   => $description !== '' ? $description : null,
            'attributes'    => $attributes,
            'brand_context' => $brandContext,
        ];
    }

    /**
     * Build the gallery breakdown directly from the structured brief that was
     * persisted on generation_meta. This is the authoritative source — the
     * exact selections the user made — so it avoids the lossy regex parsing of
     * the assembled prompt.
     *
     * @param  array  $brief  The persisted brief (description, industry, …).
     * @param  array  $meta   The full generation_meta (for brand fields).
     */
    protected function breakdownFromBrief(array $brief, array $meta): array
    {
        $labels = [
            'industry'    => 'Industry',
            'objective'   => 'Objective',
            'tone'        => 'Tone',
            'style'       => 'Visual Style',
            'colorScheme' => 'Color Scheme',
            'platform'    => 'Platform',
            'goal'        => 'Goal',
            'motionType'  => 'Camera/Motion',
            'mood'        => 'Mood',
            'headline'    => 'Headline',
            'cta'         => 'CTA',
        ];

        $attributes = [];
        foreach ($labels as $key => $label) {
            $value = trim((string) ($brief[$key] ?? ''));
            if ($value !== '') {
                $attributes[] = [
                    'label' => $label,
                    'value' => str_replace(['_', '-'], ' ', $value),
                ];
            }
        }

        // Brand context line from the persisted brand fields, if any.
        $brandBits = array_filter([
            ! empty($meta['brandName']) ? "Brand: {$meta['brandName']}" : null,
            ! empty($meta['brandTagline']) ? "Tagline: \"{$meta['brandTagline']}\"" : null,
            ! empty($meta['brandPrimaryColor']) ? "Primary: {$meta['brandPrimaryColor']}" : null,
            ! empty($meta['brandSecondaryColor']) ? "Secondary: {$meta['brandSecondaryColor']}" : null,
        ]);

        $description = trim((string) ($brief['description'] ?? ''));

        return [
            'description'   => $description !== '' ? $description : null,
            'attributes'    => $attributes,
            'brand_context' => $brandBits !== [] ? implode(', ', $brandBits) : null,
        ];
    }
}
