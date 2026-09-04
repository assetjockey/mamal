<?php

namespace Modules\AppLinkBioAI\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAIStudio\Support\AIStudioAccess;
use Modules\AppAIStudio\Support\AiContentStudioService;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Support\LinkBioAccess;
use Throwable;

#[Title('AI Bio Assistant')]
class AIAssistantIndex extends Component
{
    use AIStudioAccess;

    public string $aiBioBrief = '';

    public string $aiBioTone = 'professional';

    public string $aiBioLanguage = 'en';

    public array $aiBioDraft = [];

    public function mount(): void
    {
        $this->aiBioTone = (string) $this->aiStudioSetting('default_tone', 'professional');
        $this->aiBioLanguage = (string) $this->aiStudioSetting('default_language', (string) (app()->getLocale() ?: 'en'));
    }

    public function generateAiBio(AiContentStudioService $studio): void
    {
        $access = app(LinkBioAccess::class);

        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseAiBio(auth()->user()), 403);

        $planOwner = $this->aiStudioPlanOwner();

        $validated = $this->validate([
            'aiBioBrief' => ['required', 'string', 'min:8', 'max:1200'],
            'aiBioTone' => ['required', Rule::in(['professional', 'friendly', 'sales', 'educational', 'bold', 'casual'])],
            'aiBioLanguage' => ['required', 'string', 'max:12'],
        ], [], [
            'aiBioBrief' => __('AI bio brief'),
            'aiBioTone' => __('tone'),
            'aiBioLanguage' => __('language'),
        ]);

        try {
            if (function_exists('credit_service')) {
                credit_service()->ensureCanConsume($planOwner, 'link_bio_ai_assistant');
            }

            $this->aiBioDraft = $this->normalizeAiBioDraft($studio->generateLinkBioDraft($validated['aiBioBrief'], [
                'tone' => $validated['aiBioTone'],
                'language' => $validated['aiBioLanguage'],
                ...$this->aiStudioWorkspacePromptConfig(),
            ]));

            if (function_exists('consume_credits')) {
                consume_credits($planOwner, 'link_bio_ai_assistant', [
                    'feature' => 'link-bio.ai-assistant',
                    'metadata' => [
                        'language' => $validated['aiBioLanguage'],
                        'tone' => $validated['aiBioTone'],
                    ],
                ]);
            }

            $this->dispatch('app-toast', type: 'success', message: __('AI bio draft generated.'));
        } catch (Throwable $exception) {
            $this->aiBioDraft = [];
            $this->addError('aiBioBrief', $exception->getMessage());
        }
    }

    public function createFromAiBioDraft(LinkBioAccess $access): void
    {
        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseAiBio(auth()->user()), 403);
        abort_unless($access->canCreate(auth()->user()), 403);

        if ($this->aiBioDraft === []) {
            $this->addError('aiBioBrief', __('Generate an AI bio draft first.'));

            return;
        }

        $title = trim((string) data_get($this->aiBioDraft, 'title', '')) ?: __('My Link Bio');
        $blocks = $this->sanitizeBlocks((array) data_get($this->aiBioDraft, 'blocks', []));
        $defaultStatus = $access->defaultStatus();

        $page = LinkBioPage::query()->create([
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'headline' => trim((string) data_get($this->aiBioDraft, 'headline', '')) ?: null,
            'description' => trim((string) data_get($this->aiBioDraft, 'description', '')) ?: null,
            'accent_color' => '#2563eb',
            'avatar_url' => null,
            'cover_url' => null,
            'template_key' => $access->defaultTemplateKey(),
            'is_published' => $defaultStatus === 'published',
            'blocks' => $blocks !== [] ? $blocks : [$this->defaultBlock('links')],
            'settings' => [
                'branding_text' => $access->defaultBrandingText(),
                'avatar_style' => 'circle',
                'button_style' => 'rounded',
                'content_align' => 'left',
            ],
            'owner_user_id' => $access->workspaceOwnerUserId(auth()->user()),
            'team_id' => $access->currentTeamId(auth()->user()),
        ]);

        if (LinkBioPage::hasStatusColumn()) {
            $page->update(['status' => $defaultStatus]);
        }

        $this->dispatch('app-toast', type: 'success', message: __('AI bio page created.'));
        $this->redirectRoute('portal.link-bio.edit', ['page' => $page->id], navigate: true);
    }

    public function render(LinkBioAccess $access): View
    {
        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseAiBio(auth()->user()), 403);

        return view('applinkbioai::index', [
            'canCreate' => $access->canCreate(auth()->user()),
            'aiBioCreditPreview' => $this->aiStudioCreditPreview('link_bio_ai_assistant'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('AI Bio Assistant'),
        ]);
    }

    protected function normalizeAiBioDraft(array $draft): array
    {
        return [
            'title' => Str::limit(trim((string) data_get($draft, 'title', '')), 80, ''),
            'headline' => Str::limit(trim((string) data_get($draft, 'headline', '')), 160, ''),
            'description' => Str::limit(trim((string) data_get($draft, 'description', '')), 400, ''),
            'blocks' => $this->sanitizeBlocks((array) data_get($draft, 'blocks', [])),
        ];
    }

    protected function sanitizeBlocks(array $blocks): array
    {
        return collect($blocks)
            ->filter(fn ($block) => in_array((string) data_get($block, 'type', 'links'), ['links', 'video', 'social', 'header', 'contact', 'gallery', 'embed', 'faq', 'product'], true))
            ->map(function ($block): array {
                $type = (string) data_get($block, 'type', 'links');
                $items = collect((array) data_get($block, 'items', []))
                    ->map(fn ($item) => [
                        'label' => trim((string) data_get($item, 'label', '')),
                        'url' => trim((string) data_get($item, 'url', '')),
                        'note' => trim((string) data_get($item, 'note', '')),
                        'icon' => trim((string) data_get($item, 'icon', '')),
                        'image' => trim((string) data_get($item, 'image', '')),
                        'value' => trim((string) data_get($item, 'value', '')),
                        'price' => trim((string) data_get($item, 'price', '')),
                        'placeholder' => trim((string) data_get($item, 'placeholder', '')),
                        'answer' => trim((string) data_get($item, 'answer', '')),
                        'field_type' => trim((string) data_get($item, 'field_type', 'text')),
                    ])
                    ->filter(fn (array $item) => collect($item)->some(fn ($value) => $value !== ''))
                    ->values()
                    ->all();

                return [
                    'type' => $type,
                    'title' => trim((string) data_get($block, 'title', '')),
                    'subtitle' => trim((string) data_get($block, 'subtitle', '')),
                    'content' => trim((string) data_get($block, 'content', '')),
                    'url' => trim((string) data_get($block, 'url', '')),
                    'button_label' => trim((string) data_get($block, 'button_label', '')),
                    'button_url' => trim((string) data_get($block, 'button_url', '')),
                    'enabled' => (bool) data_get($block, 'enabled', true),
                    'items' => $items,
                ];
            })
            ->filter(fn (array $block) => $block['enabled'] || $block['title'] !== '' || $block['content'] !== '' || $block['items'] !== [])
            ->values()
            ->all();
    }

    protected function defaultBlock(string $type): array
    {
        return [
            'type' => $type,
            'title' => Str::headline($type),
            'subtitle' => '',
            'content' => '',
            'url' => '',
            'button_label' => '',
            'button_url' => '',
            'enabled' => true,
            'items' => [[
                'label' => __('Item'),
                'note' => __('Supporting text'),
                'url' => 'https://example.com',
                'icon' => 'fa-solid fa-link',
                'image' => '',
                'value' => '',
                'price' => '',
                'placeholder' => '',
                'answer' => '',
                'field_type' => 'text',
            ]],
        ];
    }

    protected function uniqueSlug(string $value): string
    {
        $slug = Str::slug($value) ?: 'link-bio';
        $base = $slug;
        $counter = 2;

        while (LinkBioPage::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
