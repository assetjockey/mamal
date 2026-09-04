<?php

namespace App\Livewire\User\CopyStudio;

use App\Models\AdCopy;
use App\Models\Brand;
use App\Services\AdCopy\CopyGenerator;
use App\Services\AdCopy\Support\EngineRegistry;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Title('Ad Text Generation')]
class CopyStudio extends Component
{
    use \App\Livewire\Concerns\ResolvesProjectContext;

    // Form
    #[Url(as: 'platform')]
    public string $platform = 'facebook_feed';
    public string $objective = 'sales';
    public string $framework = 'aida';
    public string $tone = 'bold';
    public string $engine = 'openai';
    public string $model = '';
    public string $language = 'en';

    public string $productDescription = '';
    public string $targetAudience = '';
    public string $keyBenefits = '';
    public string $keywords = '';
    public string $cta = '';
    public string $extraInstructions = '';

    public int $variantsCount = 1;

    public ?int $brandId = null;
    public array $availableBrands = [];

    // State
    public int $creditBalance = 0;
    public ?int $latestCopyId = null;

    // Reuse from library
    #[Url(as: 'reuse')]
    public ?int $reuse = null;

    public function mount(CreditServiceInterface $credits): mixed
    {
        if (! \App\Services\HelperService::accessCopyStudio()) {
            if (\App\Services\HelperService::studioLockedCopyStudio() && \App\Services\HelperService::studioUpgradeAvailable()) {
                Toaster::warning(__('Copy Studio is not included in your current plan. Upgrade to unlock it.'));

                return $this->redirect(\App\Services\HelperService::studioUpgradeUrl(), navigate: true);
            }

            Toaster::warning(__('Copy Studio is not available on your plan.'));

            return $this->redirectRoute('user.dashboard', navigate: true);
        }

        $user = auth()->user();

        // Respect admin-configured default engine, but only if it actually
        // has enabled models in the registry; otherwise fall back to the
        // first available engine.
        $available = EngineRegistry::availableEngines();
        $defaultEngine = \App\Models\FeatureSetting::first()?->default_copy_engine;
        if ($defaultEngine && isset($available[$defaultEngine])) {
            $this->engine = $defaultEngine;
        } elseif (! isset($available[$this->engine])) {
            $this->engine = (string) (array_key_first($available) ?? $this->engine);
        }

        // Always start on a model that's actually enabled for this engine.
        $this->model = EngineRegistry::defaultModel($this->engine) ?? '';

        $this->creditBalance = $credits->getBalance($user);

        $this->availableBrands = Brand::where('user_id', $user->id)
            ->where('is_active', true)
            ->get(['id','name','industry','logo_path','primary_color','secondary_color','is_default'])
            ->toArray();
        $default = collect($this->availableBrands)->firstWhere('is_default', true);
        $this->brandId = $default['id'] ?? ($this->availableBrands[0]['id'] ?? null);

        // Project context: when launched from a Project workspace (?project={id}),
        // default the new copy's brand to the Project's Brand_Kit. Applies only
        // when the project has an owned Brand_Kit; otherwise the user's own
        // default stands (Requirements 10.8, 10.10). A later user change to the
        // brand selector overrides this default (10.9).
        $this->resolveProjectContext();
        if ($projectBrandId = $this->projectDefaultBrandId()) {
            $this->brandId = $projectBrandId;
        }

        if ($this->reuse) {
            $this->reusePrevious($this->reuse);
        }

        return null;
    }

    /**
     * When the engine value syncs to the server (deferred — e.g. on the next
     * Livewire request such as Generate), only snap the model to the engine's
     * default if the current model isn't valid for the new engine. The picker
     * itself is client-side, so normally the model is already correct here.
     */
    public function updatedEngine(string $value): void
    {
        if (! EngineRegistry::isModelEnabled($value, $this->model)) {
            $this->model = EngineRegistry::defaultModel($value) ?? '';
        }
    }

    public function reusePrevious(int $id): void
    {
        $copy = AdCopy::where('user_id', auth()->id())->find($id);
        if (! $copy) return;

        $this->platform = $copy->platform;
        $this->objective = $copy->objective ?? $this->objective;
        $this->framework = $copy->framework ?? $this->framework;
        $this->tone = $copy->tone ?? $this->tone;
        $this->engine = $copy->engine ?? $this->engine;
        $this->language = $copy->language ?? 'en';
        $this->productDescription = $copy->product_description ?? '';
        $this->targetAudience = $copy->target_audience ?? '';
        $this->keyBenefits = $copy->key_benefits ?? '';
        $this->keywords = $copy->keywords ?? '';
        $this->cta = $copy->cta ?? '';
        $this->extraInstructions = $copy->extra_instructions ?? '';
        $this->brandId = $copy->brand_id;
    }

    public function generate(CopyGenerator $generator, CreditServiceInterface $credits): void
    {
        $availableEngines = array_keys(EngineRegistry::availableEngines());
        $enabledModels    = array_keys(EngineRegistry::enabledModels($this->engine));

        // Snap the model back to a valid one before validating, in case the
        // admin disabled it between mount and submit.
        if (! in_array($this->model, $enabledModels, true)) {
            $this->model = EngineRegistry::defaultModel($this->engine) ?? '';
            $enabledModels = array_keys(EngineRegistry::enabledModels($this->engine));
        }

        $this->validate([
            'platform'            => 'required|string',
            'productDescription'  => 'required|string|max:2000',
            'engine'              => ['required', 'string', \Illuminate\Validation\Rule::in($availableEngines)],
            'model'               => ['required', 'string', \Illuminate\Validation\Rule::in($enabledModels)],
            'variantsCount'       => 'required|integer|min:1|max:6',
        ]);

        $user = auth()->user();

        // Copy is priced per 1000 words; we can't know the exact word count
        // until generation completes, so gate on at least the model's per-1k
        // rate (one pricing unit) up front.
        if (! $credits->hasSufficientCredits($user, 'copy', $this->model, \App\Services\AiStudio\CreditService::COPY_WORD_UNIT)) {
            Toaster::warning(__('Not enough credits. Upgrade your plan to continue generating.'));
            return;
        }

        try {
            $record = $generator->generate($user, [
                'brand_id'            => $this->brandId,
                'project_id'          => $this->projectId,
                'platform'            => $this->platform,
                'objective'           => $this->objective,
                'framework'           => $this->framework,
                'tone'                => $this->tone,
                'engine'              => $this->engine,
                'model'               => $this->model,
                'language'            => $this->language,
                'product_description' => $this->productDescription,
                'target_audience'     => $this->targetAudience,
                'key_benefits'        => $this->keyBenefits,
                'keywords'            => $this->keywords,
                'cta'                 => $this->cta,
                'extra_instructions'  => $this->extraInstructions,
                'variants'            => $this->variantsCount,
            ]);

            $this->latestCopyId = $record->id;
            $this->creditBalance = $credits->getBalance($user);

            Toaster::success(__('Ad Copy generated'));
        } catch (\Throwable $e) {
            toaster()
                ->error(__('Generation failed: :msg', ['msg' => $e->getMessage()]))
                ->duration(5000);
        }
    }

    public function toggleFavorite(int $id): void
    {
        $copy = AdCopy::where('user_id', auth()->id())->find($id);
        if (! $copy) return;
        $copy->update(['is_favorite' => ! $copy->is_favorite]);
    }

    public function getPlatformsByGroupProperty(): array
    {
        $groups = config('ad-copy.platform_groups', []);
        $platforms = config('ad-copy.platforms', []);

        $grouped = [];
        foreach ($groups as $key => $meta) {
            $grouped[$key] = [
                'meta' => $meta,
                'platforms' => collect($platforms)
                    ->filter(fn ($p) => ($p['group'] ?? null) === $key)
                    ->all(),
            ];
        }
        return $grouped;
    }

    /**
     * The per-1000-words credit rate for the currently selected copy model.
     * Used by the cost chip and the low-balance warning.
     */
    public function getCopyCostProperty(): int
    {
        return app(\App\Services\AiStudio\Contracts\CreditServiceInterface::class)
            ->getRate('copy', $this->model ?: null);
    }

    /**
     * Engines exposed to the user — only those that have at least one enabled model.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getEnginesAvailableProperty(): array
    {
        $engines = EngineRegistry::availableEngines();
        $available = [];
        foreach ($engines as $key => $engine) {
            $enabled = $engine['enabled_models'] ?? [];
            if (! empty($enabled)) {
                $available[$key] = $engine;
            }
        }
        return $available;
    }

    /**
     * Models the user can pick for the currently selected engine.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getModelsAvailableProperty(): array
    {
        return EngineRegistry::enabledModels($this->engine);
    }

    public function getLatestCopyProperty(): ?AdCopy
    {
        return $this->latestCopyId
            ? AdCopy::where('user_id', auth()->id())->find($this->latestCopyId)
            : null;
    }

    public function getRecentCopiesProperty()
    {
        return AdCopy::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->latest()
            ->take(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.user.copy-studio.copy-studio', [
            'copyCost' => $this->copyCost,
            // Owner-scoped projects offered in the Launch section so the user
            // can drop the generated copy straight into a project. Null/empty
            // selection means the copy stays unassigned.
            'availableProjects' => \App\Models\Project::where('user_id', auth()->id())
                ->orderByDesc('updated_at')
                ->get(['id', 'name']),
        ]);
    }
}
