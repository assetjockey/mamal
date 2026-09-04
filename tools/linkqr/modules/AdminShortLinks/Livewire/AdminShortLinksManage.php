<?php

namespace Modules\AdminShortLinks\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AdminUser\Models\User;
use Modules\AppShortLinks\Models\AppShortLink;

#[Title('Manage Short Links')]
class AdminShortLinksManage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all';

    public int $perPage = 25;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $linkId): void
    {
        AppShortLink::query()->whereKey($linkId)->update([
            'moderation_status' => 'approved',
            'moderation_note' => null,
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('Short link approved.'));
    }

    public function block(int $linkId): void
    {
        AppShortLink::query()->whereKey($linkId)->update([
            'moderation_status' => 'blocked',
            'status' => 'paused',
            'moderation_note' => __('Blocked by admin moderation.'),
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('Short link blocked.'));
    }

    public function restore(int $linkId): void
    {
        AppShortLink::query()->whereKey($linkId)->update([
            'moderation_status' => 'approved',
            'status' => 'active',
            'moderation_note' => null,
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('Short link restored.'));
    }

    public function render(): View
    {
        $query = AppShortLink::query()
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $search = trim($this->search);
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('short_code', 'like', '%'.$search.'%')
                        ->orWhere('destination_url', 'like', '%'.$search.'%')
                        ->orWhere('campaign', 'like', '%'.$search.'%')
                        ->orWhere('folder', 'like', '%'.$search.'%');
                });
            })
            ->when($this->status !== 'all', function (Builder $query): void {
                if ($this->status === 'blocked') {
                    $query->where('moderation_status', 'blocked');
                } elseif ($this->status === 'reported') {
                    $query->where('moderation_status', 'pending');
                } else {
                    $query->where('status', $this->status);
                }
            });

        $links = $query
            ->latest()
            ->paginate($this->perPage);
        $owners = User::query()
            ->whereIn('id', $links->getCollection()->pluck('owner_user_id')->filter()->unique())
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        return view('adminshortlinks::manage', [
            'links' => $links,
            'owners' => $owners,
            'metrics' => [
                'links' => AppShortLink::query()->count(),
                'active' => AppShortLink::query()->where('status', 'active')->count(),
                'blocked' => AppShortLink::query()->where('moderation_status', 'blocked')->count(),
                'reported' => AppShortLink::query()->where('moderation_status', 'pending')->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Manage Short Links')]);
    }
}
