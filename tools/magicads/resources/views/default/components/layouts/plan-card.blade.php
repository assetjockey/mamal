@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    if (! $user) return;

    // Plan / credits / upgrade card is part of the SaaS billing feature.
    // Hide it entirely when the magicads-saas plugin is not active.
    if (! \App\Services\HelperService::extensionSaaS()) return;

    $sub = $user->activeSubscription()->with('plan')->first();
    $planLabel = $sub?->plan?->name ?? __('Free');
    $planCredits = (int) ($sub?->plan?->credits ?? 0);
    $isTopTier = $sub && (int) $sub->plan?->price > 0;

    // Current remaining credits: combined spendable balance (plan credits +
    // prepaid/gift credits), matching what actually gets spent on generations.
    $remaining = (int) $user->creditBalance();

    // Upper bound for the usage bar: plan's allocation if present, otherwise the
    // current balance. Never let it drop below `remaining` (prepaid credits can
    // push the balance above the plan allocation) so `used` stays non-negative.
    $allocation = max($planCredits, $remaining, 1);
    $used = max(0, $allocation - $remaining);
    $pctUsed = $allocation > 0 ? (int) round(min(100, ($used / $allocation) * 100)) : 0;
    $pctLeft = 100 - $pctUsed;

    // Upgrade destination: admins manage plans; users go to the in-app billing
    // page when available, otherwise fall back to the public pricing page, then
    // a homepage anchor, then the site root.
    if ($user->hasRole('admin')) {
        $upgradeHref = route('admin.finance.plans');
    } elseif (Route::has('user.billing')) {
        $upgradeHref = route('user.billing');
    } elseif (Route::has('pricing')) {
        $upgradeHref = route('pricing');
    } elseif (Route::has('home')) {
        $upgradeHref = route('home') . '#pricing';
    } else {
        $upgradeHref = url('/');
    }

    // Visual tone: low credits → warning / danger accents (semantic, allowed by palette rules)
    $tone = match (true) {
        $pctLeft <= 10 => 'danger',
        $pctLeft <= 25 => 'warning',
        default        => 'neutral',
    };

    // Brand gradients (locked — see .kiro/steering/brand-palette.md). Semantic tones stay neutral-semantic.
    $barStyle = match ($tone) {
        'danger'  => 'background: linear-gradient(90deg, #F43F5E, #E11D48);',    // rose → red (critical)
        'warning' => 'background: linear-gradient(90deg, #F59E0B, #D97706);',    // amber (attention)
        default   => 'background: linear-gradient(90deg, #4F46E5, #F59E0B);',    // brand cool → warm (indigo-600 → amber-500)
    };
@endphp

{{-- Plan card — hidden when sidebar is collapsed; stays compact in the sidebar footer --}}
<div class="px-3 pb-2 in-data-flux-sidebar-collapsed-desktop:hidden">
    <div class="relative overflow-hidden rounded-xl border border-zinc-200/70 dark:border-white/5 bg-white/70 dark:bg-white/2 backdrop-blur-sm p-3">

        {{-- subtle top rail (brand cool) --}}
        <div class="absolute top-0 inset-x-0 h-px"
             style="background: linear-gradient(90deg, transparent, rgba(79,70,229,0.45), transparent);"></div>

        {{-- Header: plan name + optional "Upgrade" link --}}
        <div class="flex items-center justify-between gap-2 mb-2">
            <div class="flex items-center gap-1.5 min-w-0">
                <flux:icon.sparkles class="size-3.5 shrink-0" style="color: #4F46E5;" />
                <span class="truncate text-[11px] font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300">{{ $planLabel }}</span>
            </div>
            @unless($isTopTier)
                <a href="{{ $upgradeHref }}" class="shrink-0 text-[10px] font-semibold hover:underline" style="color: #4F46E5;">{{ __('Upgrade') }}</a>
            @endunless
        </div>

        {{-- Credit progress --}}
        <div class="space-y-1.5">
            <div class="flex items-baseline justify-between gap-2">
                <div class="flex items-baseline gap-1">
                    <flux:icon.bolt class="size-3 text-amber-500 self-center" />
                    <span class="text-sm font-black text-zinc-800 dark:text-zinc-100 tabular-nums">{{ number_format($remaining) }}</span>
                    @if($planCredits > 0)
                        <span class="text-[10px] text-zinc-400 tabular-nums">/ {{ number_format($planCredits) }}</span>
                    @endif
                </div>
                <span class="text-[10px] font-semibold {{ $tone === 'danger' ? 'text-rose-600 dark:text-rose-400' : ($tone === 'warning' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-500') }}">
                    {{ __('credits') }}
                </span>
            </div>

            <div class="relative h-1.5 rounded-full bg-zinc-100 dark:bg-white/5 overflow-hidden">
                <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-700"
                     style="width: {{ $pctLeft }}%; {{ $barStyle }}"></div>
            </div>
        </div>

        {{-- Contextual CTA: upgrade if low/free, otherwise "Add credits" --}}
        <div class="mt-2.5">
            @if($tone === 'danger' || ! $sub)
                <a href="{{ $upgradeHref }}"
                   class="group relative flex items-center justify-center gap-1.5 w-full px-3 py-1.5 rounded-lg text-white text-[11px] font-semibold shadow-sm hover:shadow-md transition overflow-hidden"
                   style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                    <flux:icon.rocket-launch class="size-3" />
                    {{ $sub ? __('Top up credits') : __('Upgrade plan') }}
                </a>
            @else
                <a href="{{ $upgradeHref }}"
                   class="flex items-center justify-center gap-1.5 w-full px-3 py-1.5 rounded-lg text-[11px] font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-white/5 hover:bg-zinc-200 dark:hover:bg-white/10 transition">
                    <flux:icon.plus class="size-3" />
                    {{ __('Add credits') }}
                </a>
            @endif
        </div>
    </div>
</div>
