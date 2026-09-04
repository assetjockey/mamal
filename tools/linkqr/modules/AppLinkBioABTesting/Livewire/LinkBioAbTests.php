<?php

namespace Modules\AppLinkBioABTesting\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Support\LinkBioAccess;

#[Title('Link Bio A/B Tests')]
class LinkBioAbTests extends Component
{
    #[Url(as: 'page', except: 0)]
    public int $pageId = 0;

    public array $items = [];

    public function mount(): void
    {
        abort_unless(app(LinkBioAccess::class)->enabled(auth()->user()), 404);
        $this->loadPageItems();
    }

    public function updatedPageId(): void
    {
        $this->loadPageItems();
    }

    public function save(): void
    {
        $page = $this->page();

        if (! $page) {
            return;
        }

        $blocks = (array) $page->blocks;

        foreach ($this->items as $blockIndex => $blockItems) {
            foreach ((array) $blockItems as $itemIndex => $payload) {
                if (! isset($blocks[$blockIndex]['items'][$itemIndex])) {
                    continue;
                }

                data_set($blocks, "{$blockIndex}.items.{$itemIndex}.utm_content", trim((string) data_get($payload, 'utm_content', '')));
                data_set($blocks, "{$blockIndex}.items.{$itemIndex}.ab_variants", $this->sanitizeVariants((array) data_get($payload, 'ab_variants', [])));
            }
        }

        $page->update(['blocks' => $blocks]);
        $this->dispatch('app-toast', type: 'success', message: __('A/B tests saved.'));
        $this->loadPageItems();
    }

    public function clearItem(int $blockIndex, int $itemIndex): void
    {
        data_set($this->items, "{$blockIndex}.{$itemIndex}.utm_content", '');
        data_set($this->items, "{$blockIndex}.{$itemIndex}.ab_variants", []);
    }

    public function render(): View
    {
        $pages = $this->pages();

        if ($this->pageId <= 0 && $pages->isNotEmpty()) {
            $this->pageId = (int) $pages->first()->id;
            $this->loadPageItems();
        }

        return view('applinkbioabtesting::index', [
            'pages' => $pages,
            'page' => $this->page(),
            'stats' => $this->stats(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('A/B Tests')]);
    }

    protected function loadPageItems(): void
    {
        $page = $this->page();

        if (! $page) {
            $this->items = [];

            return;
        }

        $this->items = collect((array) $page->blocks)
            ->mapWithKeys(function (array $block, int $blockIndex): array {
                $items = collect((array) data_get($block, 'items', []))
                    ->filter(fn (array $item): bool => trim((string) data_get($item, 'url', '')) !== '')
                    ->mapWithKeys(function (array $item, int $itemIndex) use ($block): array {
                        return [$itemIndex => [
                            'block_title' => trim((string) data_get($block, 'title', '')),
                            'label' => trim((string) data_get($item, 'label', __('Item'))),
                            'note' => trim((string) data_get($item, 'note', '')),
                            'url' => trim((string) data_get($item, 'url', '')),
                            'utm_content' => trim((string) data_get($item, 'utm_content', '')),
                            'ab_variants' => $this->ensureTwoVariants((array) data_get($item, 'ab_variants', [])),
                        ]];
                    })
                    ->all();

                return $items === [] ? [] : [$blockIndex => $items];
            })
            ->all();
    }

    protected function stats(): array
    {
        $flatItems = collect($this->items)->flatMap(fn (array $blockItems): array => $blockItems);
        $enabledVariants = $flatItems
            ->flatMap(fn (array $item): array => (array) data_get($item, 'ab_variants', []))
            ->filter(fn (array $variant): bool => (bool) data_get($variant, 'enabled', false))
            ->count();

        return [
            'buttons' => $flatItems->count(),
            'tests' => $flatItems->filter(fn (array $item): bool => collect((array) data_get($item, 'ab_variants', []))->contains(fn (array $variant): bool => (bool) data_get($variant, 'enabled', false)))->count(),
            'variants' => $enabledVariants,
            'utm' => $flatItems->filter(fn (array $item): bool => filled(data_get($item, 'utm_content')))->count(),
        ];
    }

    protected function ensureTwoVariants(array $variants): array
    {
        $variants = $this->sanitizeVariants($variants);

        foreach ([0 => 'A', 1 => 'B'] as $index => $key) {
            $variants[$index] ??= [
                'key' => $key,
                'enabled' => false,
                'weight' => 50,
                'label' => '',
                'url' => '',
                'note' => '',
            ];
        }

        return array_values($variants);
    }

    protected function sanitizeVariants(array $variants): array
    {
        return collect($variants)
            ->filter(fn ($variant): bool => is_array($variant))
            ->take(4)
            ->map(function (array $variant, int $index): array {
                return [
                    'key' => trim((string) data_get($variant, 'key', chr(65 + $index))) ?: chr(65 + $index),
                    'enabled' => (bool) data_get($variant, 'enabled', false),
                    'weight' => max(1, min(100, (int) data_get($variant, 'weight', 50))),
                    'label' => trim((string) data_get($variant, 'label', '')),
                    'url' => trim((string) data_get($variant, 'url', '')),
                    'note' => trim((string) data_get($variant, 'note', '')),
                ];
            })
            ->values()
            ->all();
    }

    protected function page(): ?LinkBioPage
    {
        if ($this->pageId <= 0) {
            return null;
        }

        return $this->pages()->firstWhere('id', $this->pageId);
    }

    protected function pages()
    {
        return LinkBioPage::query()
            ->where('owner_user_id', app(LinkBioAccess::class)->workspaceOwnerUserId(auth()->user()))
            ->latest()
            ->get();
    }
}
