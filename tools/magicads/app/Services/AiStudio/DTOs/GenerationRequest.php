<?php

namespace App\Services\AiStudio\DTOs;

class GenerationRequest
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $type,
        public readonly string $provider,
        public readonly string $presetSlug,
        public readonly int $width,
        public readonly int $height,
        public readonly ?int $duration = null,

        // Selected quality tier for engines that expose one (e.g. Seedance:
        // '480p' | '720p' | '1080p' | '4k'). Null for engines that derive
        // resolution from the pixel dimensions instead. Persisted so the
        // stateless cron poller can bill the right per-tier credit rate.
        public readonly ?string $resolution = null,

        public readonly ?string $referenceImagePath = null,
        public readonly ?int $brandKitId = null,

        // Overlay text fields — used by the post-processing step that
        // burns user-typed headline + CTA into the generated asset.
        // Both are optional; when null/empty the overlay step is a no-op
        // for that field.
        public readonly ?string $overlayText = null,
        public readonly ?string $ctaText = null,

        // Brand colors snapshot, captured from the user's brand kit at
        // dispatch time so the overlay layer doesn't have to query the
        // database. Hex strings, e.g. "#4F46E5". Null falls back to the
        // defaults in config('ai-studio.overlay').
        public readonly ?string $brandPrimaryColor = null,
        public readonly ?string $brandSecondaryColor = null,

        // Per-generation override for the post-processing overlay step.
        //   - null  → use the engine default (skip for native-text engines
        //             like Veo, run for weak-text engines like Runway/Kling).
        //   - true  → force overlay regardless of engine. User wants
        //             FFmpeg-rendered text even on Veo.
        //   - false → skip overlay regardless of engine. User trusts the
        //             model and wants to save the FFmpeg time.
        public readonly ?bool $forceOverlay = null,

        // ── Brand identity, passed through so image drivers can faithfully
        // reproduce the brand in the render (not just describe it). ──────────

        // Disk-relative path (on the 'public' disk) to the brand logo, e.g.
        // "brands/12/logo.png". When set AND the selected engine can ingest
        // image inputs, the driver attaches the logo so the model composites
        // the real mark into the ad rather than inventing one.
        public readonly ?string $brandLogoPath = null,

        public readonly ?string $brandName = null,
        public readonly ?string $brandTagline = null,

        // Raw, unflattened creative brief (the user's structured selections +
        // free-text description). Persisted on generation_meta so the gallery
        // can show a clean breakdown and "reuse" can recover the exact
        // description without regex-parsing the assembled prompt.
        public readonly ?array $brief = null,

        // The owned Project this creative is being generated within, when the
        // studio was launched from a Project workspace. Null when standalone.
        // Used to associate the resulting AdCreative with the project.
        public readonly ?int $projectId = null,
    ) {}

    /**
     * Serialize for persistence on AdCreative.generation_meta so a later,
     * stateless cron poll can rebuild the exact request without re-reading
     * the wizard. Keep keys stable — this is written to the database.
     */
    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'type' => $this->type,
            'provider' => $this->provider,
            'presetSlug' => $this->presetSlug,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'resolution' => $this->resolution,
            'referenceImagePath' => $this->referenceImagePath,
            'brandKitId' => $this->brandKitId,
            'overlayText' => $this->overlayText,
            'ctaText' => $this->ctaText,
            'brandPrimaryColor' => $this->brandPrimaryColor,
            'brandSecondaryColor' => $this->brandSecondaryColor,
            'forceOverlay' => $this->forceOverlay,
            'brandLogoPath' => $this->brandLogoPath,
            'brandName' => $this->brandName,
            'brandTagline' => $this->brandTagline,
            'brief' => $this->brief,
            'projectId' => $this->projectId,
        ];
    }

    /**
     * Rebuild a request from the persisted array. Tolerant of missing keys
     * so older rows (written before a field existed) still rehydrate.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            prompt: (string) ($data['prompt'] ?? ''),
            type: (string) ($data['type'] ?? 'video'),
            provider: (string) ($data['provider'] ?? ''),
            presetSlug: (string) ($data['presetSlug'] ?? ''),
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            resolution: $data['resolution'] ?? null,
            referenceImagePath: $data['referenceImagePath'] ?? null,
            brandKitId: isset($data['brandKitId']) ? (int) $data['brandKitId'] : null,
            overlayText: $data['overlayText'] ?? null,
            ctaText: $data['ctaText'] ?? null,
            brandPrimaryColor: $data['brandPrimaryColor'] ?? null,
            brandSecondaryColor: $data['brandSecondaryColor'] ?? null,
            forceOverlay: isset($data['forceOverlay']) ? (bool) $data['forceOverlay'] : null,
            brandLogoPath: $data['brandLogoPath'] ?? null,
            brandName: $data['brandName'] ?? null,
            brandTagline: $data['brandTagline'] ?? null,
            brief: isset($data['brief']) && is_array($data['brief']) ? $data['brief'] : null,
            projectId: isset($data['projectId']) ? (int) $data['projectId'] : null,
        );
    }
}
