<?php

namespace App\Services\AiStudio;

use App\Models\Brand;
use App\Models\BrandKit;
use Illuminate\Support\Str;

/**
 * Composes a professional, model-ready advertising prompt.
 *
 * Earlier versions simply stitched the raw user inputs together
 * ("Industry: x. Tone: y. {text}") and appended a "Brand context:" line.
 * Modern image models (Nano Banana 2, GPT Image 2, Ideogram 3, FLUX.2)
 * respond far better to a *directed brief* — the kind a human art director
 * would write — than to a flat list of labels. This builder turns the
 * wizard's structured selections into that brief: a single coherent
 * instruction describing the scene, composition, lighting, brand treatment,
 * typography, and quality bar.
 *
 * The public API is intentionally backward compatible: the legacy
 * build(string, $brand, $preset) signature still works. The richer path is
 * buildAdImagePrompt(array $brief, $brand, $preset) which the Image Studio
 * now calls with the full set of inputs.
 */
class PromptBuilder
{
    /**
     * Legacy entry point — preserves the original behaviour for the Video
     * Studio (and any other caller): the user prompt with an appended brand
     * context block. The advanced, image-specific art-direction composer is
     * buildAdImagePrompt(), which the Image Studio calls.
     *
     * @param  BrandKit|Brand|null  $brand
     */
    public function build(string $userPrompt, $brand = null, ?array $preset = null): string
    {
        $parts = [$userPrompt];

        if ($preset) {
            $parts[] = "Canvas: {$preset['label']} ({$preset['width']}x{$preset['height']}, {$preset['ratio']})";
        }

        if ($brand instanceof Brand) {
            $context = $brand->toPromptContext();
            if ($context !== '') {
                $parts[] = 'Brand context: '.$context;
            }

            return implode("\n", $parts);
        }

        if ($brand instanceof BrandKit) {
            $brandParts = [];

            if ($brand->primary_color) {
                $brandParts[] = "Primary color: {$brand->primary_color}";
            }
            if ($brand->secondary_color) {
                $brandParts[] = "Secondary color: {$brand->secondary_color}";
            }
            if ($brand->tagline) {
                $brandParts[] = "Brand tagline: {$brand->tagline}";
            }

            if (! empty($brandParts)) {
                $parts[] = 'Brand context: '.implode(', ', $brandParts);
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Build a high-quality advertising image prompt from a structured brief.
     *
     * Recognised $brief keys (all optional except a description or some
     * meaningful signal):
     *   description, industry, objective, tone, style, colorScheme,
     *   headline, cta, useBrandKit (bool)
     *
     * @param  BrandKit|Brand|null  $brand  Brand to lock the render to, or null.
     */
    public function buildAdImagePrompt(array $brief, $brand = null, ?array $preset = null): string
    {
        $description = trim((string) ($brief['description'] ?? ''));
        $industry    = $this->clean($brief['industry'] ?? null);
        $objective   = $this->clean($brief['objective'] ?? null);
        $tone        = $this->clean($brief['tone'] ?? null);
        $style       = $this->clean($brief['style'] ?? null);
        $colorScheme = $this->clean($brief['colorScheme'] ?? null);
        $headline    = trim((string) ($brief['headline'] ?? ''));
        $cta         = trim((string) ($brief['cta'] ?? ''));

        $useBrand = ! empty($brief['useBrandKit']) && $brand !== null;
        $brandData = $useBrand ? $this->brandData($brand) : null;
        // Whether the logo file is actually being attached to the model call
        // (engine-dependent). Defaults to false — describe-only — unless the
        // caller confirms the driver will ingest it.
        $logoAttached = $useBrand && ! empty($brief['logoAttached']);

        $sections = [];

        // 1. Role + task framing. Sets the model's "mindset" the way the
        //    backend system prompts on the Gemini / ChatGPT platforms do.
        $sections[] = 'You are an expert advertising art director and commercial '
            .'photographer. Create a single, polished, production-ready advertising '
            .'image that could run as a paid social or display ad with no further editing.';

        // 2. The subject / scene — the user's own description leads, because
        //    it is the most specific signal we have.
        $subjectLine = $this->composeSubjectLine($description, $industry, $objective);
        if ($subjectLine !== '') {
            $sections[] = 'Subject and concept: '.$subjectLine;
        }

        // 3. Art direction — tone, style, palette folded into one directive
        //    sentence rather than disconnected labels.
        $artDirection = $this->composeArtDirection($tone, $style, $colorScheme, $brandData);
        if ($artDirection !== '') {
            $sections[] = 'Art direction: '.$artDirection;
        }

        // 4. Composition + technical quality bar. This is the biggest lever
        //    for "looks like a real ad" vs. "looks AI-generated".
        $sections[] = 'Composition and quality: '.$this->composeQualityDirective($preset);

        // 5. Brand treatment — colors, tagline, and crucially the LOGO.
        if ($brandData) {
            $sections[] = 'Brand treatment: '.$this->composeBrandDirective($brandData, $logoAttached);
        }

        // 6. Typography — only when the user actually wants rendered copy.
        $typography = $this->composeTypography($headline, $cta);
        if ($typography !== '') {
            $sections[] = 'Typography: '.$typography;
        }

        // 7. Canvas / format awareness so the model fills the frame for the
        //    target placement instead of centering a square in a banner.
        if ($preset) {
            $sections[] = 'Output format: '.$this->composeFormatDirective($preset);
        }

        // 8. Negative guidance — the cheapest quality win across every model.
        $sections[] = 'Avoid: '.$this->negativePrompt($headline !== '' || $cta !== '');

        return implode("\n\n", array_filter($sections));
    }

    /**
     * Compose the subject sentence: the user's free text, contextualised by
     * industry and objective when present, without leaking raw "key: value"
     * labels into the model input.
     */
    protected function composeSubjectLine(string $description, ?string $industry, ?string $objective): string
    {
        $parts = [];

        if ($description !== '') {
            $parts[] = rtrim($description, '. ').'.';
        }

        $context = [];
        if ($industry) {
            $context[] = "the {$industry} industry";
        }
        if ($objective) {
            $context[] = "an ad whose goal is {$objective}";
        }

        if ($context !== []) {
            $lead = $description === '' ? 'A professional advertising visual for ' : 'This is for ';
            $parts[] = $lead.$this->humanJoin($context).'.';
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Fold tone, visual style and color palette into a single art-direction
     * directive. When a brand kit is active its palette wins over the generic
     * colorScheme to avoid contradictory color instructions.
     */
    protected function composeArtDirection(?string $tone, ?string $style, ?string $colorScheme, ?array $brandData): string
    {
        $parts = [];

        if ($style) {
            $parts[] = "{$style} visual style";
        }
        if ($tone) {
            $parts[] = "a {$tone} mood and tone";
        }

        // Brand palette takes precedence; fall back to the chosen color scheme.
        if ($brandData && ($brandData['primary'] || $brandData['secondary'])) {
            $colors = array_filter([$brandData['primary'], $brandData['secondary']]);
            $parts[] = 'a color palette built around '.$this->humanJoin($colors)
                .' (the brand colors), used purposefully across the scene, props and lighting';
        } elseif ($colorScheme) {
            $parts[] = "a {$colorScheme} color palette";
        }

        if ($parts === []) {
            return '';
        }

        return 'Render with '.$this->humanJoin($parts).'. Keep it cohesive and intentional, like a campaign shot by a top agency.';
    }

    /**
     * The quality / realism directive. This is generic on purpose — it is the
     * shared "make it look real and premium" instruction every strong model
     * benefits from.
     */
    protected function composeQualityDirective(?array $preset): string
    {
        return 'Use a clear focal hierarchy with the hero subject sharply in focus, '
            .'deliberate negative space for text placement, balanced rule-of-thirds composition, '
            .'realistic physically-based lighting with soft natural shadows and accurate reflections, '
            .'high dynamic range, crisp micro-detail and textures, and true-to-life color. '
            .'Photographic, ultra-detailed, sharp, professionally lit and color-graded. '
            .'The result must look like a real commercial photograph or polished design, not an AI render.';
    }

    /**
     * Brand treatment, with explicit, forceful logo instructions. When a logo
     * is attached as an image input (driver dependent) we tell the model to
     * use that exact mark; otherwise we still describe its placement so the
     * composition leaves room for it.
     */
    protected function composeBrandDirective(array $brandData, bool $logoAttached = false): string
    {
        $parts = [];

        if ($brandData['name']) {
            $parts[] = "This ad is for the brand \"{$brandData['name']}\"";
        }

        $colors = array_filter([$brandData['primary'], $brandData['secondary']]);
        if ($colors !== []) {
            $parts[] = 'use the brand colors '.$this->humanJoin($colors)
                .' as the dominant palette for accents, surfaces and lighting';
        }

        if ($brandData['tagline']) {
            $parts[] = "reflect the brand tagline \"{$brandData['tagline']}\" in the overall feel";
        }

        if ($brandData['hasLogo'] && $logoAttached) {
            // The driver attaches the actual logo file as an image input. Be
            // explicit and strict so the model composites the real mark
            // instead of hallucinating a generic one.
            $parts[] = 'A reference image of the brand logo is provided — '
                .'place that EXACT logo into the ad, unaltered in shape, proportions and colors, '
                .'cleanly and legibly (typically a top or bottom corner or centered lockup), '
                .'at a tasteful size with clear surrounding space. Do not redraw, recolor, '
                .'distort, crop or reinterpret the logo, and do not invent any other logo or wordmark';
        } elseif ($brandData['hasLogo']) {
            // Engine can't ingest the file — still reserve a clean, well-lit
            // area for the brand's logo to be placed afterward.
            $parts[] = 'leave a clean, uncluttered area with even lighting where the brand logo '
                .'can sit legibly (a top or bottom corner), and do not invent or render any logo, '
                .'wordmark or brand name text yourself';
        }

        return $this->humanJoin($parts).'.';
    }

    /**
     * Typography directive for rendered headline / CTA copy. We ask for the
     * exact strings, quoted, with spelling locked — the failure mode on weaker
     * engines is garbled text, so the instruction is deliberately strict.
     */
    protected function composeTypography(string $headline, string $cta): string
    {
        $parts = [];

        if ($headline !== '') {
            $parts[] = "render the headline exactly as \"{$headline}\" in bold, modern, "
                .'highly legible typography as the primary text element';
        }
        if ($cta !== '') {
            $parts[] = "include a clear call-to-action button or label reading exactly \"{$cta}\"";
        }

        if ($parts === []) {
            return '';
        }

        return ucfirst($this->humanJoin($parts))
            .'. Spell all text perfectly with correct kerning and no gibberish, '
            .'extra letters or duplicated words. Integrate the text into the layout '
            .'with strong contrast against its background so it stays readable.';
    }

    /**
     * Canvas-aware framing so the model composes for the actual placement.
     */
    protected function composeFormatDirective(array $preset): string
    {
        $label = $preset['label'] ?? 'ad';
        $ratio = $preset['ratio'] ?? null;
        $w = $preset['width'] ?? null;
        $h = $preset['height'] ?? null;

        $line = "Designed for a {$label} placement";
        if ($ratio) {
            $line .= " at a {$ratio} aspect ratio";
        }
        if ($w && $h) {
            $line .= " ({$w}x{$h}px)";
        }
        $line .= '. Fill the entire frame edge to edge with an intentional layout for this exact shape; '
            .'do not letterbox, pad or center a square inside the frame.';

        return $line;
    }

    /**
     * Shared negative prompt. Extra text-related items only when the user
     * asked for rendered copy (so we don't tell a no-text ad to avoid
     * "misspelled text" and confuse the model).
     */
    protected function negativePrompt(bool $hasText): string
    {
        $items = [
            'low resolution', 'blur', 'noise', 'jpeg artifacts', 'watermarks',
            'stock-photo overlays', 'distorted or extra limbs and fingers',
            'warped faces', 'unrealistic proportions', 'muddy or washed-out colors',
            'cluttered or unbalanced composition', 'amateur lighting', 'a flat AI-render look',
        ];

        if ($hasText) {
            $items[] = 'misspelled, garbled or duplicated text';
            $items[] = 'fake or placeholder lorem-ipsum copy';
        } else {
            $items[] = 'any unrequested text, captions or watermarks';
        }

        return implode(', ', $items).'.';
    }

    /**
     * Normalise a brand entity (Brand or legacy BrandKit) into a flat array.
     */
    protected function brandData($brand): array
    {
        if ($brand instanceof Brand) {
            return [
                'name'     => $brand->name ?: null,
                'tagline'  => $brand->tagline ?: null,
                'primary'  => $brand->primary_color ?: null,
                'secondary'=> $brand->secondary_color ?: null,
                'hasLogo'  => filled($brand->logo_path),
            ];
        }

        if ($brand instanceof BrandKit) {
            return [
                'name'     => null,
                'tagline'  => $brand->tagline ?: null,
                'primary'  => $brand->primary_color ?: null,
                'secondary'=> $brand->secondary_color ?: null,
                'hasLogo'  => filled($brand->logo_path),
            ];
        }

        return [
            'name' => null, 'tagline' => null, 'primary' => null,
            'secondary' => null, 'hasLogo' => false,
        ];
    }

    /**
     * Turn a slug/raw selection ("product_launch", "dark-moody") into a
     * readable phrase ("product launch", "dark moody"). Returns null for
     * empty input so callers can skip it cleanly.
     */
    protected function clean($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return Str::lower(str_replace(['_', '-'], ' ', $value));
    }

    /**
     * Join a list into natural English: "a, b and c".
     */
    protected function humanJoin(array $parts): string
    {
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));

        if ($parts === []) {
            return '';
        }
        if (count($parts) === 1) {
            return $parts[0];
        }

        $last = array_pop($parts);

        return implode(', ', $parts).' and '.$last;
    }
}
