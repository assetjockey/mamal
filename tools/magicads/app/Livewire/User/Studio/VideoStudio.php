<?php

namespace App\Livewire\User\Studio;

use App\Models\AdCreative;
use App\Models\Brand;
use App\Services\AiStudio\AdGenerator;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\PromptBuilder;
use App\Services\AiStudio\ProviderManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

#[Title('Video Studio')]
class VideoStudio extends Component
{
    use WithFileUploads;
    use \App\Livewire\Concerns\ResolvesProjectContext;

    // Core fields
    public string $prompt = '';

    public string $selectedModel = '';

    public string $selectedPreset = '';

    public int $selectedDuration = 5;

    /**
     * Selected quality tier ('480p'|'720p'|'1080p'|'4k') for engines that
     * expose one (e.g. Seedance). Empty string when the current engine has no
     * selectable tiers — resolution is then derived from the chosen format.
     */
    public string $selectedResolution = '';

    public $referenceImage;

    // Ad industry fields
    public string $videoGoal = '';

    public string $videoStyle = '';

    public string $motionType = '';

    public string $platform = '';

    public string $videoMood = '';

    public string $overlayText = '';

    public string $ctaText = '';

    /**
     * Per-generation override for the FFmpeg overlay post-processing.
     *   - null → use the engine default (off for Veo, on for Runway/Kling/Seedance)
     *   - true → force the overlay regardless of engine
     *   - false → skip the overlay regardless of engine
     */
    public ?bool $forceOverlay = null;

    // Brand selection
    public ?int $selectedBrandId = null;

    public array $availableBrands = [];

    // State
    public array $providers = [];

    public array $presets = [];

    public array $durations = [];

    public int $creditBalance = 0;

    public bool $isGenerating = false;

    /**
     * The AdCreative created by *this* wizard session. The Step 5 status
     * panel (generating / failed / result) is scoped to this id so a fresh
     * visit shows the Review & Generate screen instead of resurrecting the
     * user's previously completed video. Null until the user clicks Generate.
     */
    public ?int $currentAssetId = null;

    public function mount(ProviderManager $providerManager, CreditServiceInterface $creditService): mixed
    {
        if (! \App\Services\HelperService::accessVideoStudio()) {
            if (\App\Services\HelperService::studioLockedVideoStudio() && \App\Services\HelperService::studioUpgradeAvailable()) {
                Toaster::warning(__('Video Studio is not included in your current plan. Upgrade to unlock it.'));

                return $this->redirect(\App\Services\HelperService::studioUpgradeUrl(), navigate: true);
            }

            Toaster::warning(__('Video Studio is not available on your plan.'));

            return $this->redirectRoute('user.dashboard', navigate: true);
        }

        $user = auth()->user();

        $this->providers = $providerManager->availableVideoProviders()->toArray();
        $this->presets = config('ai-studio.presets.video', []);
        $this->durations = config('ai-studio.video_durations', [4, 5, 6, 8, 10, 12, 15]);
        $this->creditBalance = $creditService->getBalance($user);

        if ($this->providers) {
            $default = collect($this->providers)->firstWhere('is_default', true)
                ?? collect($this->providers)->firstWhere('recommended', true);
            $this->selectedModel = $default ? $default['key'] : $this->providers[0]['key'];
        }

        if ($this->presets) {
            $this->selectedPreset = $this->presets[0]['slug'];
        }

        // Snap the default duration to whatever the chosen provider supports.
        $this->selectedDuration = $this->resolveInitialDuration();

        // Preselect a quality tier for engines that expose them (e.g. Seedance).
        $this->selectedResolution = $this->resolveInitialResolution();

        $this->availableBrands = $user->brands()
            ->where('is_active', true)
            ->get(['id', 'name', 'industry', 'tagline', 'logo_path', 'primary_color', 'secondary_color', 'is_default'])
            ->toArray();
        $default = collect($this->availableBrands)->firstWhere('is_default', true);
        $this->selectedBrandId = $default['id'] ?? ($this->availableBrands[0]['id'] ?? null);

        // Project context: when launched from a Project workspace (?project={id}),
        // pre-select the Project's Brand_Kit so the existing snapshot/prompt logic
        // captures it. Applies only when the project has an owned Brand_Kit;
        // otherwise the user's own default stands (Requirements 10.8, 10.10).
        // A later user change to the brand selector overrides this default (10.9).
        $this->resolveProjectContext();
        if ($projectBrandId = $this->projectDefaultBrandId()) {
            $this->selectedBrandId = $projectBrandId;
        }

        if (request()->has('reuse')) {
            $this->loadFromAsset((int) request('reuse'));
        }

        if (request()->filled('preset')) {
            $presetSlug = (string) request('preset');
            $preset = collect($this->presets)->firstWhere('slug', $presetSlug);
            if ($preset) {
                $this->selectedPreset = $preset['slug'];
            }
        }

        if (request()->filled('prompt')) {
            $this->prompt = (string) request('prompt');
        }

        return null;
    }

    /**
     * Inject a prompt chosen from the Prompt Library modal into the
     * Scene Description field.
     */
    #[On('prompt-selected')]
    public function applyLibraryPrompt(string $body): void
    {
        $this->prompt = $body;
    }

    /**
     * When the user picks a different engine, snap the selected duration
     * down to whatever that engine supports. Veo caps at 8s, Kling and
     * Seedance extend to 15s, Runway maxes at 10s — silently sliding values
     * prevents "duration not supported" errors at submit time.
     */
    public function updatedSelectedModel(string $value): void
    {
        $allowed = $this->providerDurations($value);

        if (! in_array($this->selectedDuration, $allowed, true)) {
            $this->selectedDuration = end($allowed) ?: 5;
        }

        // Snap the resolution to whatever the newly-selected engine offers.
        // Engines without quality tiers clear it (resolution then comes from
        // the chosen format), so a leftover tier can't bleed across engines.
        $tiers = array_column($this->providerResolutions($value), 'tier');

        if (! in_array($this->selectedResolution, $tiers, true)) {
            $this->selectedResolution = $this->resolveInitialResolution($value);
        }
    }

    /**
     * Cost of one generation with the currently selected engine — the
     * per-second rate multiplied by the chosen clip duration.
     * Used by the review screen and the credits chip.
     */
    public function getCurrentCostProperty(): int
    {
        return app(CreditServiceInterface::class)
            ->getCost('video', $this->selectedModel ?: null, max(1, (int) $this->selectedDuration), $this->resolutionForBilling());
    }

    /**
     * The per-second credit rate for the selected engine (no duration applied).
     * Reflects the chosen quality tier for engines that price per tier.
     */
    public function getCurrentRateProperty(): int
    {
        return app(CreditServiceInterface::class)
            ->getRate('video', $this->selectedModel ?: null, $this->resolutionForBilling());
    }

    /**
     * The enabled quality tiers for the currently-selected engine, each as
     * ['tier' => '720p', 'credit_cost' => 4]. Empty when the engine has none.
     *
     * @return array<int, array{tier:string, credit_cost:int}>
     */
    public function getCurrentResolutionsProperty(): array
    {
        return $this->providerResolutions($this->selectedModel);
    }

    /**
     * The exact set of clip lengths the currently selected engine accepts.
     * Used by the duration picker to gate visible options.
     */
    public function getCurrentDurationsProperty(): array
    {
        return $this->providerDurations($this->selectedModel);
    }

    /**
     * "native" | "weak" — drives the per-engine default for the FFmpeg
     * overlay toggle on Step 4.
     *   - native engines (Veo, Veo Lite) → toggle defaults to OFF
     *   - weak engines   (Runway, Kling, Seedance) → toggle defaults to ON
     */
    public function getCurrentTextRenderingProperty(): string
    {
        return (string) (\App\Models\MediaModel::query()
            ->where('vendor', $this->selectedModel)
            ->value('text_rendering') ?? 'weak');
    }

    /**
     * Whether the overlay step will actually run for the *current* selection,
     * accounting for both the engine's default and the user's per-generation
     * forceOverlay override. Used by the Step 5 status line so users can see
     * what they're getting before they click Generate.
     */
    public function getOverlayWillRunProperty(): bool
    {
        if ($this->forceOverlay === true) {
            return true;
        }
        if ($this->forceOverlay === false) {
            return false;
        }

        return $this->currentTextRendering !== 'native';
    }

    protected function providerDurations(?string $providerKey): array
    {
        if (! $providerKey) {
            return $this->durations;
        }

        $configured = \App\Models\MediaModel::query()
            ->where('vendor', $providerKey)
            ->value('durations');

        return is_array($configured) && $configured !== [] ? $configured : $this->durations;
    }

    protected function resolveInitialDuration(): int
    {
        $allowed = $this->providerDurations($this->selectedModel);

        // Prefer 5s if available (the universal default), otherwise the smallest.
        if (in_array(5, $allowed, true)) {
            return 5;
        }

        return (int) ($allowed[0] ?? 5);
    }

    /**
     * The enabled quality tiers for a given engine, sourced from its card
     * payload (MediaModel::enabledResolutions). Empty for engines that don't
     * expose tiers.
     *
     * @return array<int, array{tier:string, credit_cost:int}>
     */
    protected function providerResolutions(?string $providerKey): array
    {
        if (! $providerKey) {
            return [];
        }

        $provider = collect($this->providers)->firstWhere('key', $providerKey);

        return is_array($provider['resolutions'] ?? null) ? $provider['resolutions'] : [];
    }

    /**
     * Preselect a quality tier for the given (or current) engine — prefer
     * 720p, else the first enabled tier, else empty when it has no tiers.
     */
    protected function resolveInitialResolution(?string $providerKey = null): string
    {
        $tiers = array_column($this->providerResolutions($providerKey ?? $this->selectedModel), 'tier');

        if ($tiers === []) {
            return '';
        }

        return in_array('720p', $tiers, true) ? '720p' : (string) $tiers[0];
    }

    /**
     * The resolution value to use for credit pricing — null when the engine
     * has no tiers, so the flat per-second rate applies.
     */
    protected function resolutionForBilling(): ?string
    {
        return $this->selectedResolution !== '' ? $this->selectedResolution : null;
    }

    public function buildSmartPrompt(): string
    {
        $parts = [];

        if ($this->platform) {
            $parts[] = "Platform: {$this->platform} ad.";
        }
        if ($this->videoGoal) {
            $parts[] = "Goal: {$this->videoGoal}.";
        }
        if ($this->videoStyle) {
            $parts[] = "Visual style: {$this->videoStyle}.";
        }
        if ($this->motionType) {
            $parts[] = "Camera/motion: {$this->motionType}.";
        }
        if ($this->videoMood) {
            $parts[] = "Mood: {$this->videoMood}.";
        }
        if ($this->prompt) {
            $parts[] = $this->prompt;
        }
        if ($this->overlayText) {
            $parts[] = "Include text overlay: \"{$this->overlayText}\".";
        }
        if ($this->ctaText) {
            $parts[] = "End with call-to-action: \"{$this->ctaText}\".";
        }

        return implode(' ', $parts);
    }

    public function generate(CreditServiceInterface $creditService, PromptBuilder $promptBuilder, AdGenerator $adGenerator): void
    {
        $allowedDurations = $this->providerDurations($this->selectedModel);
        $allowedResolutions = array_column($this->providerResolutions($this->selectedModel), 'tier');

        $this->validate([
            'prompt' => 'required|string|max:2000',
            'selectedModel' => 'required|string',
            'selectedPreset' => 'required|string',
            'selectedDuration' => ['required', 'integer', \Illuminate\Validation\Rule::in($allowedDurations)],
            // Only enforce a tier when the engine actually exposes tiers;
            // engines without them submit an empty resolution.
            'selectedResolution' => $allowedResolutions === []
                ? ['nullable', 'string']
                : ['required', 'string', \Illuminate\Validation\Rule::in($allowedResolutions)],
            'overlayText' => 'nullable|string|max:100',
            'ctaText' => 'nullable|string|max:50',
            'forceOverlay' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $resolution = $allowedResolutions === [] ? null : $this->selectedResolution;

        if (! $creditService->hasSufficientCredits($user, 'video', $this->selectedModel, max(1, (int) $this->selectedDuration), $resolution)) {
            Toaster::warning(__('Not enough credits. Upgrade your plan to continue generating.'));

            return;
        }

        $preset = collect($this->presets)->firstWhere('slug', $this->selectedPreset);
        $smartPrompt = $this->buildSmartPrompt();

        $brand = $this->selectedBrandId
            ? Brand::where('user_id', $user->id)->find($this->selectedBrandId)
            : $user->brandKit;

        $builtPrompt = $promptBuilder->build($smartPrompt, $brand, $preset);

        $request = new GenerationRequest(
            prompt: $builtPrompt,
            type: 'video',
            provider: $this->selectedModel,
            presetSlug: $this->selectedPreset,
            width: $preset['width'] ?? 1920,
            height: $preset['height'] ?? 1080,
            duration: $this->selectedDuration,
            resolution: $resolution,
            referenceImagePath: $this->referenceImage?->store('ai-studio/references/'.$user->id, 'public'),
            brandKitId: $user->brandKit?->id,

            // Overlay fields — burned into the final clip by the
            // post-processing step in GenerateAdJob.
            overlayText: $this->overlayText !== '' ? $this->overlayText : null,
            ctaText: $this->ctaText !== '' ? $this->ctaText : null,
            brandPrimaryColor: $brand?->primary_color,
            brandSecondaryColor: $brand?->secondary_color,
            forceOverlay: $this->forceOverlay,
            projectId: $this->projectId,
        );

        $asset = $adGenerator->dispatch($user, $request);

        $this->currentAssetId = $asset->id;
        $this->isGenerating = true;
        $this->creditBalance = $creditService->getBalance($user);

        Toaster::success(__('Video generation started! This may take a few minutes...'));
    }

    public function loadFromAsset(int $assetId): void
    {
        $asset = AdCreative::where('user_id', auth()->id())->findOrFail($assetId);

        // Reuse ONLY the free-text creative description the user originally
        // typed — not the brand context, video attributes, model, preset, or
        // duration. Those are intentionally left at their fresh-session
        // defaults so the reused description starts from a clean brief.
        $this->prompt = $asset->promptBreakdown()['description'] ?? '';
    }

    public function getRecentGenerationsProperty()
    {
        return AdCreative::where('user_id', auth()->id())
            ->videos()
            ->completed()
            ->latest()
            ->take(10)
            ->get();
    }

    public function getLatestPendingProperty()
    {
        if (! $this->currentAssetId) {
            return null;
        }

        return AdCreative::where('user_id', auth()->id())
            ->videos()
            ->whereKey($this->currentAssetId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    public function getLatestFailedProperty()
    {
        if (! $this->currentAssetId) {
            return null;
        }

        $asset = AdCreative::where('user_id', auth()->id())
            ->videos()
            ->whereKey($this->currentAssetId)
            ->first();

        return $asset && $asset->status === 'failed' ? $asset : null;
    }

    /**
     * The completed video produced by *this* wizard session, if any. Drives
     * the Step 5 result/download screen. Scoped to currentAssetId so visiting
     * the studio fresh never resurrects a previously completed video here.
     */
    public function getCurrentResultProperty()
    {
        if (! $this->currentAssetId) {
            return null;
        }

        return AdCreative::where('user_id', auth()->id())
            ->videos()
            ->completed()
            ->whereKey($this->currentAssetId)
            ->first();
    }

    /**
     * Reset the entire wizard back to a clean Step 1 state for a brand-new
     * video. Clears the session asset link so the Step 5 panel returns to the
     * Review & Generate screen, and wipes all brief fields. The completed
     * video remains available under Ad Creatives / the gallery.
     */
    public function startNewVideo(): void
    {
        $this->reset([
            'prompt',
            'selectedPreset',
            'selectedDuration',
            'selectedResolution',
            'referenceImage',
            'videoGoal',
            'videoStyle',
            'motionType',
            'platform',
            'videoMood',
            'overlayText',
            'ctaText',
            'forceOverlay',
            'currentAssetId',
            'isGenerating',
        ]);

        // Re-establish sensible defaults that mount() would normally seed.
        if ($this->presets) {
            $this->selectedPreset = $this->presets[0]['slug'];
        }
        $this->selectedDuration = $this->resolveInitialDuration();
        $this->selectedResolution = $this->resolveInitialResolution();
    }

    /**
     * Mark the user's currently in-flight video as cancelled so they are not
     * stranded if the provider stalls. The cron reaper would eventually fail
     * it anyway, but this gives the user an immediate escape hatch.
     */
    public function cancelStuckGeneration(): void
    {
        $asset = $this->currentAssetId
            ? AdCreative::where('user_id', auth()->id())
                ->videos()
                ->whereKey($this->currentAssetId)
                ->whereIn('status', ['pending', 'processing'])
                ->first()
            : null;

        if (! $asset) {
            $this->isGenerating = false;

            return;
        }

        $asset->update([
            'status' => 'failed',
            'error_message' => __('Generation cancelled by user.'),
        ]);

        $this->isGenerating = false;

        Toaster::warning(__('Generation cancelled. You can edit your brief and try again.'));
    }

    public function render()
    {
        return view('livewire.user.studio.video-studio');
    }
}
