@props([
    'label' => null,
])

{{-- Consistent nav section heading — uppercase + tracked, matches the dashboard db-section-title treatment. Hidden when sidebar is collapsed.

    The wrapper around the label carries `data-sidebar-section-label` as a
    stable styling hook for resources/css/custom.css. The inner <div> carries
    Tailwind defaults; theme-specific overrides target the inner element via
    `[data-sidebar-section-label] > div`. --}}
<div {{ $attributes->class('block space-y-[2px]') }}>
    @if($label)
        <div class="px-2 pt-4 pb-1.5 in-data-flux-sidebar-collapsed-desktop:hidden" data-sidebar-section-label>
            <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ $label }}</div>
        </div>
    @endif
    <div>
        {{ $slot }}
    </div>
</div>
