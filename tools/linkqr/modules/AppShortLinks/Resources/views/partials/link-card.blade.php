<div class="rounded-[1rem] border p-4 shadow-[0_18px_45px_-38px_rgba(15,23,42,0.48)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
    <div class="flex items-start gap-3">
        <div class="inline-flex h-20 w-20 shrink-0 items-center justify-center rounded-[0.8rem] border bg-white text-xl" style="border-color: rgba(var(--theme-border-color-rgb),0.65); color: var(--theme-accent);">
            <i class="fa-light {{ $link->redirect_rules ? 'fa-route' : 'fa-link-simple' }}"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                <x-ui.badge :variant="$link->status === 'active' && $link->moderation_status !== 'blocked' ? 'success' : 'neutral'">{{ $link->moderation_status === 'blocked' ? __('Blocked') : str($link->status)->title() }}</x-ui.badge>
            </div>
            <p class="mt-2 break-all font-mono text-xs" style="color: var(--theme-header-text-color);">{{ $link->shortUrl() }}</p>
            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->destination_url }}</p>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-3 gap-2">
        <div class="rounded-[0.75rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-soft);">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Clicks') }}</p>
            <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $link->clicks_count) }}</p>
        </div>
        <div class="rounded-[0.75rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-soft);">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Campaign') }}</p>
            <p class="mt-1 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->campaign ?: '-' }}</p>
        </div>
        <div class="rounded-[0.75rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-soft);">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Created') }}</p>
            <p class="mt-1 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->created_at?->diffForHumans() }}</p>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap justify-end gap-2">
        <x-ui.button type="button" size="sm" variant="outline" x-data x-on:click="navigator.clipboard.writeText(@js($link->shortUrl()))"><i class="fa-light fa-copy"></i>{{ __('Copy') }}</x-ui.button>
        <a href="{{ route('portal.short-links.analytics', ['link' => $link->id]) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.65rem] border px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
            <i class="fa-light fa-chart-line"></i>
            {{ __('Report') }}
        </a>
        <a href="{{ route('portal.short-links.edit', $link) }}" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.65rem] border px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
            <i class="fa-light fa-pen"></i>
            {{ __('Edit') }}
        </a>
        <x-ui.button type="button" size="sm" variant="outline" wire:click="openQrModal({{ $link->id }})" wire:loading.attr="disabled" wire:target="openQrModal({{ $link->id }})">
            <i class="fa-light fa-qrcode" wire:loading.remove wire:target="openQrModal({{ $link->id }})"></i>
            <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openQrModal({{ $link->id }})"></i>
            {{ __('QR') }}
        </x-ui.button>
    </div>
</div>
