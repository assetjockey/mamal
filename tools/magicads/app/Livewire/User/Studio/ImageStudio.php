<?php

namespace App\Livewire\User\Studio;

use App\Models\AdCreative;
use App\Models\Brand;
use App\Models\BrandKit;
use App\Services\AiStudio\AdGenerator;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\ImageGenerationService;
use App\Services\AiStudio\PromptBuilder;
use App\Services\AiStudio\ProviderManager;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

#[Title('Image Studio')]
class ImageStudio extends Component
{
    use WithFileUploads;
    use \App\Livewire\Concerns\ResolvesProjectContext;

    // Core generation fields
    public string $prompt = '';

    public string $selectedModel = '';

    public string $selectedPreset = '';

    public ?int $customWidth = 1080;

    public ?int $customHeight = 1080;

    public $referenceImage;

    // A ready-made preset chosen from the dashboard "Preset Library". Holds a
    // web-relative path under /public (e.g. assets/presets/style-01.svg). Used
    // as the reference image when the user hasn't uploaded their own file.
    public ?string $presetReferencePath = null;

    public ?string $presetReferenceUrl = null;

    // Ad industry fields
    public string $adObjective = '';

    public string $adTone = '';

    public string $adIndustry = '';

    public string $adHeadline = '';

    public string $adCtaText = '';

    public string $adColorScheme = '';

    public string $adStyle = '';

    // Brand selection
    public ?int $selectedBrandId = null;

    public array $availableBrands = [];

    // Brand kit fields (legacy single kit)
    public ?string $brandLogoPath = null;

    public ?string $brandPrimaryColor = null;

    public ?string $brandSecondaryColor = null;

    public ?string $brandTagline = null;

    // Whether the brand kit (colors + tagline) is applied to this generation.
    // When false, the brand kit is neither injected into the prompt nor shown
    // on the review summary, so a chosen Color Scheme is honoured as-is.
    public bool $useBrandKit = true;

    // State
    public array $providers = [];

    public array $presets = [];

    public int $creditBalance = 0;

    public bool $isGenerating = false;

    // The pending image row queued by generate(), to be run by the follow-up
    // processQueuedImage() request. Null when nothing is mid-launch.
    public ?int $queuedImageId = null;

    // Wizard session — used to scope the result/pending screens to the
    // current run so a previous generation does not bleed into a fresh
    // wizard session on step 1.
    public ?int $sessionStartedAt = null;

    public function mount(ProviderManager $providerManager, CreditServiceInterface $creditService): mixed
    {
        if (! \App\Services\HelperService::accessImageStudio()) {
            if (\App\Services\HelperService::studioLockedImageStudio() && \App\Services\HelperService::studioUpgradeAvailable()) {
                Toaster::warning(__('Image Studio is not included in your current plan. Upgrade to unlock it.'));

                return $this->redirect(\App\Services\HelperService::studioUpgradeUrl(), navigate: true);
            }

            Toaster::warning(__('Image Studio is not available on your plan.'));

            return $this->redirectRoute('user.dashboard', navigate: true);
        }

        $user = auth()->user();

        $this->providers = $providerManager->availableImageProviders()->toArray();
        $this->presets = config('ai-studio.presets.image', []);
        $this->creditBalance = $creditService->getBalance($user);

        if ($this->providers) {
            $default = collect($this->providers)->firstWhere('is_default', true)
                ?? collect($this->providers)->firstWhere('recommended', true);
            $this->selectedModel = $default ? $default['key'] : $this->providers[0]['key'];
        }

        $brandKit = $user->brandKit;
        if ($brandKit) {
            $this->brandLogoPath = $brandKit->logo_path;
            $this->brandPrimaryColor = $brandKit->primary_color;
            $this->brandSecondaryColor = $brandKit->secondary_color;
            $this->brandTagline = $brandKit->tagline;
        }

        // Load user's brands for selection
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

        if ($this->selectedBrandId) {
            $brand = Brand::where('user_id', $user->id)->find($this->selectedBrandId);
            if ($brand) {
                $this->brandLogoPath = $brand->logo_path ?: $this->brandLogoPath;
                $this->brandPrimaryColor = $brand->primary_color ?: $this->brandPrimaryColor;
                $this->brandSecondaryColor = $brand->secondary_color ?: $this->brandSecondaryColor;
                $this->brandTagline = $brand->tagline ?: $this->brandTagline;
            }
        }

        if (request()->has('reuse')) {
            $this->loadFromAsset((int) request('reuse'));
        }

        // Deep-link from the dashboard preset library: a ready-made ad image
        // chosen as the reference. The value is the file's basename (id);
        // resolve it back to a real file under /public/assets/presets.
        if (request()->filled('ref_preset')) {
            $this->applyPresetReference((string) request('ref_preset'));
        }

        // Deep-link from dashboard preset gallery / command palette
        if (request()->filled('preset')) {
            $presetSlug = (string) request('preset');
            if ($this->findPreset($presetSlug)) {
                $this->selectedPreset = $presetSlug;
                $this->updatedSelectedPreset();
            }
        }

        if (request()->filled('prompt')) {
            $this->prompt = (string) request('prompt');
        }

        // Anchor the wizard session at the moment of mount so any previously
        // generated asset (older than this timestamp) is treated as historical
        // and does not appear on the final step.
        $this->sessionStartedAt = now()->getTimestamp();

        return null;
    }

    /**
     * Reset every per-generation field on the wizard so that landing on
     * step 1 (or clicking "New Ad" / "Reset") starts from a clean slate.
     * Brand selection, brand kit, and providers/presets metadata stay
     * because they are user/account-level configuration.
     */
    public function resetWizard(): void
    {
        $this->prompt = '';
        $this->adObjective = '';
        $this->adIndustry = '';
        $this->adTone = '';
        $this->adStyle = '';
        $this->adColorScheme = '';
        $this->adHeadline = '';
        $this->adCtaText = '';
        $this->selectedPreset = '';
        $this->customWidth = 1080;
        $this->customHeight = 1080;
        $this->referenceImage = null;
        $this->presetReferencePath = null;
        $this->presetReferenceUrl = null;
        $this->isGenerating = false;
        $this->queuedImageId = null;
        $this->useBrandKit = true;

        // Move the session window forward so previously completed assets
        // stop counting as "the result of this run".
        $this->sessionStartedAt = now()->getTimestamp();
    }

    /**
     * Prepare a regeneration: keep every brief field exactly as it is (the
     * user wants to tweak and re-run the same ad) but move the session window
     * forward and clear the in-flight flags. Without this, step 5 keeps
     * resolving the previous completed asset (getCurrentRunCompleted) and
     * shows the old result instead of a clean Review & Launch state.
     */
    public function prepareRegeneration(): void
    {
        $this->isGenerating = false;
        $this->queuedImageId = null;

        // Anchor a new session so the prior completed/pending/failed asset
        // is treated as historical and the final step renders the launch UI.
        $this->sessionStartedAt = now()->getTimestamp();
    }

    public function updatedSelectedBrandId($id): void
    {
        if (! $id) {
            return;
        }
        $brand = Brand::where('user_id', auth()->id())->find($id);
        if ($brand) {
            $this->brandLogoPath = $brand->logo_path;
            $this->brandPrimaryColor = $brand->primary_color;
            $this->brandSecondaryColor = $brand->secondary_color;
            $this->brandTagline = $brand->tagline;
            if ($brand->industry && ! $this->adIndustry) {
                $this->adIndustry = strtolower(str_replace([' & ', ' / ', ' '], ['_', '_', '_'], $brand->industry));
            }
        }
    }

    public function generate(CreditServiceInterface $creditService, PromptBuilder $promptBuilder, AdGenerator $adGenerator): void
    {
        $this->validate([
            'prompt' => 'required|string|max:5000',
            'selectedModel' => 'required|string',
            'selectedPreset' => 'required|string',
            'customWidth' => 'nullable|integer|min:256|max:4096',
            'customHeight' => 'nullable|integer|min:256|max:4096',
            'adHeadline' => 'nullable|string|max:300',
            'adCtaText' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();

        if (! $creditService->hasSufficientCredits($user, 'image', $this->selectedModel)) {
            Toaster::warning(__('Not enough credits. Upgrade your plan to continue generating.'));

            return;
        }

        [$width, $height] = $this->resolvePresetDimensions();

        // Only fold the brand kit / brand into the prompt when the user has
        // opted in. With the toggle off, the selected Color Scheme is honoured
        // as-is instead of being overridden by saved brand colors.
        $brand = null;
        if ($this->useBrandKit) {
            $brand = $this->selectedBrandId
                ? Brand::where('user_id', $user->id)->find($this->selectedBrandId)
                : ($user->brandKit);
        }

        $preset = $this->findPreset($this->selectedPreset);

        // Structured brief — the wizard's raw selections, handed to the
        // PromptBuilder so it can compose a directed, model-ready art brief
        // (instead of the old "Industry: x. Tone: y." label stitch). Also
        // persisted on the request so the gallery / reuse have clean fields.
        $brief = [
            'description' => $this->prompt,
            'industry'    => $this->adIndustry,
            'objective'   => $this->adObjective,
            'tone'        => $this->adTone,
            'style'       => $this->adStyle,
            // Color scheme only feeds the prompt when the brand kit is OFF;
            // otherwise the brand palette is the single source of truth.
            'colorScheme' => $this->useBrandKit ? null : $this->adColorScheme,
            'headline'    => $this->adHeadline,
            'cta'         => $this->adCtaText,
            'useBrandKit' => $this->useBrandKit,
        ];

        // Resolve the brand logo so drivers that accept image inputs can
        // composite the REAL logo into the ad. logo_path is stored on the
        // 'public' disk (see BrandEditor), which is the disk the drivers read.
        $brandLogoPath = ($brand && filled($brand->logo_path)) ? $brand->logo_path : null;

        // Whether the chosen engine can actually ingest the logo as an image
        // input. Drives the prompt wording (attach the exact logo vs. only
        // describe it) so we never promise the model a file it won't receive.
        $logoAttached = $brandLogoPath && \App\Models\MediaModel::vendorSupportsImageInput($this->selectedModel);
        $brief['logoAttached'] = (bool) $logoAttached;

        $builtPrompt = $promptBuilder->buildAdImagePrompt($brief, $brand, $preset);

        // Resolve the reference image. An uploaded file wins; otherwise fall
        // back to a preset chosen from the dashboard library, which we copy
        // into the public storage disk so the drivers (which read from
        // Storage::disk('public')) can load it like any uploaded reference.
        $referencePath = null;
        if ($this->referenceImage) {
            $referencePath = $this->referenceImage->store('ai-studio/references/'.$user->id, 'public');
        } elseif ($this->presetReferencePath) {
            $referencePath = $this->storePresetReference($user->id);
        }

        $request = new GenerationRequest(
            prompt: $builtPrompt,
            type: 'image',
            provider: $this->selectedModel,
            presetSlug: $this->selectedPreset,
            width: $width,
            height: $height,
            referenceImagePath: $referencePath,
            brandKitId: $this->useBrandKit
                ? ($brand instanceof BrandKit ? $brand->id : ($user->brandKit?->id))
                : null,
            brandLogoPath: $brandLogoPath,
            brandName: $brand instanceof Brand ? $brand->name : null,
            brandTagline: $brand?->tagline,
            brief: $brief,
            projectId: $this->projectId,
        );

        // Two-phase generation so the full-page "AI is creating your ad"
        // loading screen actually renders:
        //   Phase 1 (this request) — only persist the `pending` row and
        //     return immediately. The render now shows the loading UI
        //     because $this->latestPending resolves to this fresh row.
        //   Phase 2 (processQueuedImage) — a follow-up request fired by the
        //     loading screen runs the provider call inline. When it finishes
        //     the next poll/refresh flips the view to the result state.
        $asset = $adGenerator->persistPending($user, $request);

        $this->isGenerating = true;
        $this->queuedImageId = $asset->id;
        $this->creditBalance = $creditService->getBalance($user);
    }

    /**
     * Phase 2 of generation: run the provider call inline for the row queued
     * by generate(). Fired by the loading screen right after it renders, so
     * the user sees the full-page progress UI for the whole render instead of
     * just a spinner inside the button. If this request itself exceeds the
     * sync budget the image is left `pending` and the cron fallback finishes
     * it — identical end state, no double run.
     */
    public function processQueuedImage(ImageGenerationService $images): void
    {
        $assetId = $this->queuedImageId;
        $this->queuedImageId = null;

        if (! $assetId) {
            return;
        }

        $asset = AdCreative::where('user_id', auth()->id())
            ->images()
            ->whereIn('status', ['pending', 'processing'])
            ->find($assetId);

        if (! $asset) {
            return;
        }

        $images->generateNow($asset);

        $this->creditBalance = app(CreditServiceInterface::class)->getBalance(auth()->user());

        match ($asset->fresh()->status) {
            'completed' => Toaster::success(__('Your ad is ready!')),
            'failed' => Toaster::error(__('That generation did not go through. Please try again.')),
            default => Toaster::success(__('Your ad is on its way — it will appear here automatically.')),
        };
    }

    public function loadFromAsset(int $assetId): void
    {
        $asset = AdCreative::where('user_id', auth()->id())->findOrFail($assetId);

        // Reuse ONLY the free-text creative description the user originally
        // typed — not the brand context, ad attributes, model, preset, or
        // dimensions. Those are intentionally left at their fresh-session
        // defaults so the reused description is dropped into a clean brief.
        // promptBreakdown() strips the labelled segments (Industry, Tone,
        // Canvas, Brand context, …) back out of the assembled prompt.
        $this->prompt = $asset->promptBreakdown()['description'] ?? '';
    }

    /**
     * Resolve a preset id (basename of a file in /public/assets/presets) back
     * to a real, web-relative path and remember it as the active reference.
     * Guards against traversal by only matching files actually in the folder.
     */
    public function applyPresetReference(string $presetId): void
    {
        $dir = public_path('assets/presets');

        if (! is_dir($dir)) {
            return;
        }

        // A slot id looks like "preset-01". The dropped-in file may be named
        // after that id ("preset-01.png") OR after its 1-based index
        // ("1.png"), matching the dashboard gallery resolver. Accept both.
        $accepted = [$presetId];
        if (preg_match('/^preset-0*(\d+)$/', $presetId, $m)) {
            $accepted[] = (string) ((int) $m[1]);
        }

        $files = glob($dir.DIRECTORY_SEPARATOR.'*.{png,jpg,jpeg,webp,gif,svg}', GLOB_BRACE) ?: [];

        foreach ($files as $file) {
            if (in_array(pathinfo($file, PATHINFO_FILENAME), $accepted, true)) {
                $rel = 'assets/presets/'.basename($file);
                $this->presetReferencePath = $rel;
                $this->presetReferenceUrl = asset($rel);

                return;
            }
        }
    }

    /**
     * Drop the preset reference so the user can fall back to uploading their
     * own file (or generate with no reference at all).
     */
    public function clearPresetReference(): void
    {
        $this->presetReferencePath = null;
        $this->presetReferenceUrl = null;
    }

    /**
     * Copy the selected preset image from /public/assets/presets into the
     * public storage disk under the user's references folder, returning the
     * disk-relative path the generation drivers expect. Returns null if the
     * preset file can't be read.
     */
    protected function storePresetReference(int $userId): ?string
    {
        if (! $this->presetReferencePath) {
            return null;
        }

        $source = public_path($this->presetReferencePath);

        if (! is_file($source)) {
            return null;
        }

        $bytes = @file_get_contents($source);
        if ($bytes === false) {
            return null;
        }

        $ext = pathinfo($source, PATHINFO_EXTENSION) ?: 'png';
        $target = 'ai-studio/references/'.$userId.'/preset-'.uniqid().'.'.$ext;

        Storage::disk('public')->put($target, $bytes);

        return $target;
    }

    /**
     * Mark the user's currently-pending generation as cancelled so they
     * are not stranded if the queue worker is down or the job timed out.
     */
    public function cancelStuckGeneration(): void
    {
        $asset = AdCreative::where('user_id', auth()->id())
            ->images()
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();

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

    /**
     * Run the latest pending generation synchronously in the request thread.
     * Useful when the queue worker is offline (local dev, low-traffic
     * deployments) so users do not have to wait indefinitely.
     */
    public function runPendingGenerationNow(CreditServiceInterface $credits): void
    {
        $asset = AdCreative::where('user_id', auth()->id())
            ->images()
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();

        if (! $asset) {
            return;
        }

        try {
            $request = new GenerationRequest(
                prompt: $asset->prompt,
                type: $asset->type,
                provider: $asset->provider,
                presetSlug: $asset->preset_slug,
                width: $asset->width,
                height: $asset->height,
                referenceImagePath: null,
                brandKitId: null,
            );

            // Resolve the job and run it inline so any CreditService binding
            // injects correctly. dispatchSync would also work but we avoid
            // the queue layer entirely here for clearer error surfacing.
            $job = new \App\Jobs\GenerateAdJob($asset->id, $request);
            app()->call([$job, 'handle']);

            $this->creditBalance = $credits->getBalance(auth()->user());
            Toaster::success(__('Generation completed.'));
        } catch (\Throwable $e) {
            $asset->refresh();
            if ($asset->status !== 'failed') {
                $asset->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
            Toaster::error(__('Generation failed: ').$e->getMessage());
        }
    }

    public function saveBrandKit(): void
    {
        $this->validate([
            'brandTagline' => 'nullable|string|max:120',
            'brandPrimaryColor' => 'nullable|string|max:7',
            'brandSecondaryColor' => 'nullable|string|max:7',
        ]);

        $user = auth()->user();

        // When a brand is selected, the inline fields represent edits to *that*
        // brand, so persist them back to the Brand record. This matches what the
        // UI implies (the fields are populated from the selected brand). Only
        // fall back to the legacy single BrandKit when no brand is selected.
        $brand = $this->selectedBrandId
            ? Brand::where('user_id', $user->id)->find($this->selectedBrandId)
            : null;

        if ($brand) {
            $brand->update([
                'primary_color' => $this->brandPrimaryColor,
                'secondary_color' => $this->brandSecondaryColor,
                'tagline' => $this->brandTagline,
            ]);

            // Keep the in-memory brand list in sync so the selector swatches and
            // labels reflect the save without a full page refresh.
            $this->availableBrands = collect($this->availableBrands)
                ->map(function (array $b) use ($brand): array {
                    if ((int) $b['id'] === (int) $brand->id) {
                        $b['primary_color'] = $brand->primary_color;
                        $b['secondary_color'] = $brand->secondary_color;
                        $b['tagline'] = $brand->tagline;
                    }

                    return $b;
                })
                ->all();

            Toaster::success(__('Brand ":name" updated successfully.', ['name' => $brand->name]));

            return;
        }

        BrandKit::updateOrCreate(
            ['user_id' => $user->id],
            [
                'primary_color' => $this->brandPrimaryColor,
                'secondary_color' => $this->brandSecondaryColor,
                'tagline' => $this->brandTagline,
                'logo_path' => $this->brandLogoPath,
            ]
        );

        Toaster::success(__('Brand kit saved successfully.'));
    }

    public function updatedSelectedPreset(): void
    {
        $preset = $this->findPreset($this->selectedPreset);
        if ($preset) {
            $this->customWidth = $preset['width'];
            $this->customHeight = $preset['height'];
        }
    }

    public function getRecentGenerationsProperty()
    {
        return AdCreative::where('user_id', auth()->id())
            ->images()
            ->completed()
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * The freshly-completed asset for *this* wizard session, used to drive
     * the "Your ad is ready!" state on step 5. Returns null if the most
     * recent completion is older than the session start, so previous runs
     * never bleed through.
     */
    public function getCurrentRunCompletedProperty()
    {
        $cutoff = $this->sessionStartedAt
            ? \Carbon\Carbon::createFromTimestamp($this->sessionStartedAt)
            : null;

        $query = AdCreative::where('user_id', auth()->id())
            ->images()
            ->completed();

        if ($cutoff) {
            $query->where('created_at', '>=', $cutoff);
        }

        return $query->latest()->first();
    }

    public function getLatestPendingProperty()
    {
        $cutoff = $this->sessionStartedAt
            ? \Carbon\Carbon::createFromTimestamp($this->sessionStartedAt)
            : null;

        $query = AdCreative::where('user_id', auth()->id())
            ->images()
            ->whereIn('status', ['pending', 'processing']);

        if ($cutoff) {
            $query->where('created_at', '>=', $cutoff);
        }

        return $query->latest()->first();
    }

    public function getLatestFailedProperty()
    {
        // Show a failed-state alert only if there is a failed generation
        // belonging to the current wizard session.
        $cutoff = $this->sessionStartedAt
            ? \Carbon\Carbon::createFromTimestamp($this->sessionStartedAt)
            : null;

        $query = AdCreative::where('user_id', auth()->id())->images();

        if ($cutoff) {
            $query->where('created_at', '>=', $cutoff);
        }

        $latest = $query->latest()->first();

        return $latest && $latest->status === 'failed' ? $latest : null;
    }

    public function render()
    {
        return view('livewire.user.studio.image-studio');
    }

    public function resolvePresetDimensions(): array
    {
        if ($this->selectedPreset === 'custom') {
            return [(int) $this->customWidth, (int) $this->customHeight];
        }

        $preset = $this->findPreset($this->selectedPreset);

        return $preset ? [$preset['width'], $preset['height']] : [1080, 1080];
    }

    public function findPreset(string $slug): ?array
    {
        foreach (config('ai-studio.presets.image', []) as $group) {
            foreach ($group as $preset) {
                if ($preset['slug'] === $slug) {
                    return $preset;
                }
            }
        }

        return null;
    }

    /**
     * Cost in credits for one generation with the currently selected engine.
     * Used by the review screen and the credits chip in the hero stats panel.
     */
    public function getCurrentCostProperty(): int
    {
        return app(CreditServiceInterface::class)
            ->getCost('image', $this->selectedModel ?: null);
    }

    /**
     * Largest side the currently selected engine produces natively. Drives
     * the upper bound on the custom-size slider so users don't request a
     * 4096px render on a model that caps at 2048.
     */
    public function getCurrentMaxResolutionProperty(): int
    {
        $cap = (int) (\App\Models\MediaModel::query()
            ->where('vendor', $this->selectedModel)
            ->value('max_resolution') ?? 0);

        return $cap ?: (int) config('ai-studio.custom_preset_limits.max', 4096);
    }

    /**
     * "best" | "good" | "weak" — used by the headline-input warning band.
     * Engines marked "weak" trigger the "text rendering is unreliable on this
     * engine — switch to Ideogram or GPT Image 2" message when a headline is
     * entered.
     */
    public function getCurrentTextRenderingProperty(): string
    {
        return (string) (\App\Models\MediaModel::query()
            ->where('vendor', $this->selectedModel)
            ->value('text_rendering') ?? 'good');
    }

    /**
     * Human-readable name of the brand that will actually drive this
     * generation. Mirrors the resolution order used in generate(): the
     * selected Brand wins, otherwise the legacy single BrandKit, otherwise
     * null. Used on the review step to spell out exactly which brand applies.
     */
    public function getActiveBrandNameProperty(): ?string
    {
        if (! $this->useBrandKit) {
            return null;
        }

        if ($this->selectedBrandId) {
            $brand = collect($this->availableBrands)->firstWhere('id', $this->selectedBrandId);
            if ($brand && ! empty($brand['name'])) {
                return $brand['name'];
            }
        }

        return auth()->user()?->brandKit ? __('Saved brand kit') : null;
    }
}
