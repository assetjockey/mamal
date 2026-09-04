@props([
    'icon' => 'cog-6-tooth',
    'eyebrow' => null,
    'heading' => '',
    'subheading' => '',
])

{{--
    Shared settings shell used by the security pages (Password, Two-Factor).
    Mirrors the redesigned Profile hub: studio-dark gradient header, a clear
    bordered content card, and a quick link back to the profile hub. Replaces
    the old left-nav `x-settings.layout` so every account surface feels cohesive.
--}}
<section class="mx-auto w-full max-w-3xl" data-settings-shell>
    <style>
        [data-settings-shell] .ss-darkgrad {
            background-color: #09090b; /* zinc-950 */
            background-image:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(79,70,229,0.26), transparent),
                radial-gradient(ellipse 80% 50% at 110% 110%, rgba(245,158,11,0.16), transparent),
                radial-gradient(ellipse 60% 40% at 50% 50%, rgba(79,70,229,0.10), transparent);
        }
    </style>

    {{-- Header — studio dark gradient, bordered --}}
    <div class="ss-darkgrad relative overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40">
        <div class="absolute inset-x-0 top-0 h-px"
             style="background: linear-gradient(90deg, transparent, rgba(79,70,229,0.60), transparent);"></div>

        <div class="relative flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-7">
            <div class="flex items-start gap-4">
                {{-- Icon frame with glow, matching the studio hero --}}
                <div class="relative shrink-0">
                    <div class="pointer-events-none absolute -inset-2 rounded-3xl bg-indigo-500/30 blur-2xl"></div>
                    <div class="relative flex size-12 items-center justify-center rounded-2xl border border-white/15 bg-white/8 shadow-xl shadow-indigo-500/30 backdrop-blur-sm">
                        <flux:icon :name="$icon" class="size-6 text-indigo-300" />
                    </div>
                </div>
                <div class="min-w-0">
                    @if($eyebrow)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/8 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.18em] text-white">
                            {{ $eyebrow }}
                        </span>
                    @endif
                    <h1 class="mt-2 text-xl font-black tracking-tight text-white sm:text-2xl">{{ $heading }}</h1>
                    @if($subheading)
                        <p class="mt-1 max-w-lg text-xs text-zinc-400">{{ $subheading }}</p>
                    @endif
                </div>
            </div>

            <a href="{{ route('profile.edit') }}" wire:navigate
               class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-lg border border-white/15 bg-white/8 px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm transition hover:bg-white/15">
                <flux:icon.arrow-left class="size-3.5" /> {{ __('Back to profile') }}
            </a>
        </div>
    </div>

    {{-- Content card --}}
    <div class="mt-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-white/8 dark:bg-(--default-element-bg-color) sm:p-6">
        {{ $slot }}
    </div>
</section>
