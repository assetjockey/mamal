<?php

namespace Modules\AdminShortLinks\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AdminUser\Models\User;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppShortLinks\Models\AppShortLinkAbuseReport;

#[Title('Short Links')]
class AdminShortLinksIndex extends Component
{
    public ?int $clientId = null;

    public string $clientSearch = '';

    public string $period = 'days';

    public function updatedPeriod(string $value): void
    {
        if (! in_array($value, ['days', 'months'], true)) {
            $this->period = 'days';
        }
    }

    public function clearClient(): void
    {
        $this->clientId = null;
        $this->clientSearch = '';
    }

    public function render(): View
    {
        $selectedClient = $this->selectedClient();
        $linksQuery = $this->linksQuery($selectedClient?->id);
        $clicksQuery = $this->clicksQuery($selectedClient?->id);
        $last7Start = CarbonImmutable::now()->subDays(6)->startOfDay();
        $last30Start = CarbonImmutable::now()->subDays(29)->startOfDay();
        $clicks = (clone $clicksQuery)
            ->with('shortLink:id,name,short_code,destination_url,owner_user_id,clicks_count')
            ->latest('created_at')
            ->limit(5000)
            ->get();
        $ownerIds = $clicks->pluck('owner_user_id')
            ->filter()
            ->unique()
            ->values();
        $owners = User::query()
            ->whereIn('id', $ownerIds)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        return view('adminshortlinks::index', [
            'clientOptions' => $this->clientOptions($selectedClient),
            'selectedClient' => $selectedClient,
            'owners' => $owners,
            'metrics' => [
                'links' => (clone $linksQuery)->count(),
                'clients' => (clone $linksQuery)->distinct('owner_user_id')->count('owner_user_id'),
                'active' => (clone $linksQuery)->where('status', 'active')->count(),
                'blocked' => (clone $linksQuery)->where('moderation_status', 'blocked')->count(),
                'clicks' => (clone $clicksQuery)->count(),
                'clicks_7d' => (clone $clicksQuery)->where('created_at', '>=', $last7Start)->count(),
                'clicks_30d' => (clone $clicksQuery)->where('created_at', '>=', $last30Start)->count(),
                'unique_clicks' => $clicks->pluck('ip_address')->filter()->unique()->count(),
                'abuse_reports' => AppShortLinkAbuseReport::query()
                    ->where('status', 'open')
                    ->when($selectedClient?->id, fn (Builder $query, int $clientId) => $query->whereHas('shortLink', fn (Builder $nested) => $nested->where('owner_user_id', $clientId)))
                    ->count(),
            ],
            'chart' => $this->chart($clicks, $this->period),
            'topClients' => $this->topClients($selectedClient?->id),
            'topLinks' => (clone $linksQuery)->orderByDesc('clicks_count')->latest()->limit(8)->get(),
            'countries' => $this->topValues($clicks, fn ($click) => $click->country ?: __('Unknown'), 6),
            'devices' => $this->topValues($clicks, fn ($click) => str(data_get($click->metadata, 'device') ?: __('Unknown'))->title()->value(), 6),
            'referrers' => $this->topValues($clicks, fn ($click) => $this->sourceLabel($click->referer), 6),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Short Links')]);
    }

    private function selectedClient(): ?User
    {
        if (! $this->clientId) {
            return null;
        }

        return User::query()->whereKey($this->clientId)->first(['id', 'name', 'email']);
    }

    private function linksQuery(?int $clientId = null): Builder
    {
        return AppShortLink::query()
            ->when($clientId, fn (Builder $query) => $query->where('owner_user_id', $clientId));
    }

    private function clicksQuery(?int $clientId = null): Builder
    {
        return AppShortLinkClick::query()
            ->when($clientId, fn (Builder $query) => $query->where('owner_user_id', $clientId));
    }

    private function clientOptions(?User $selectedClient): Collection
    {
        $search = trim($this->clientSearch);
        $ownerIds = AppShortLink::query()
            ->distinct()
            ->pluck('owner_user_id')
            ->filter()
            ->values();

        $clients = User::query()
            ->whereIn('id', $ownerIds)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'email']);

        if ($selectedClient && ! $clients->contains('id', $selectedClient->id)) {
            $clients->prepend($selectedClient);
        }

        return $clients->values();
    }

    private function chart(Collection $clicks, string $period): array
    {
        $now = CarbonImmutable::now();

        if ($period === 'months') {
            $start = $now->subMonths(11)->startOfMonth();
            $points = collect(range(0, 11))->map(fn (int $step) => $start->addMonths($step));
            $grouped = $clicks
                ->filter(fn ($click) => $click->created_at && $click->created_at->greaterThanOrEqualTo($start))
                ->groupBy(fn ($click) => $click->created_at->format('Y-m'));

            return $points->map(fn (CarbonImmutable $date) => [
                'label' => $date->format('M Y'),
                'clicks' => $grouped->get($date->format('Y-m'), collect())->count(),
            ])->all();
        }

        $start = $now->subDays(13)->startOfDay();
        $points = collect(range(0, 13))->map(fn (int $step) => $start->addDays($step));
        $grouped = $clicks
            ->filter(fn ($click) => $click->created_at && $click->created_at->greaterThanOrEqualTo($start))
            ->groupBy(fn ($click) => $click->created_at->toDateString());

        return $points->map(fn (CarbonImmutable $date) => [
            'label' => $date->format('M j'),
            'clicks' => $grouped->get($date->toDateString(), collect())->count(),
        ])->all();
    }

    private function topClients(?int $clientId = null): Collection
    {
        if ($clientId) {
            return collect();
        }

        $linkCounts = AppShortLink::query()
            ->selectRaw('owner_user_id, COUNT(*) as links_count, SUM(clicks_count) as clicks_sum')
            ->groupBy('owner_user_id')
            ->orderByDesc('clicks_sum')
            ->limit(8)
            ->get();
        $owners = User::query()
            ->whereIn('id', $linkCounts->pluck('owner_user_id')->filter())
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        return $linkCounts->map(fn ($row): array => [
            'id' => (int) $row->owner_user_id,
            'name' => $owners[(int) $row->owner_user_id]->name ?? __('Unknown client'),
            'email' => $owners[(int) $row->owner_user_id]->email ?? null,
            'links' => (int) $row->links_count,
            'clicks' => (int) $row->clicks_sum,
        ]);
    }

    private function topValues(Collection $clicks, callable $resolver, int $limit = 6): Collection
    {
        $total = max(1, $clicks->count());

        return $clicks
            ->map(fn ($click) => trim((string) $resolver($click)) ?: __('Unknown'))
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count, string $label) => [
                'label' => $label,
                'count' => $count,
                'percent' => round(($count / $total) * 100, 1),
            ])
            ->values();
    }

    private function sourceLabel(?string $referer): string
    {
        if (! $referer) {
            return __('Direct');
        }

        return parse_url($referer, PHP_URL_HOST) ?: $referer;
    }
}
