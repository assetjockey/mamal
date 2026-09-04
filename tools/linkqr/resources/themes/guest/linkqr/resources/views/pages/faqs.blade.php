@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="linkqr-shell linkqr-section pt-10">
        <div class="grid gap-8 lg:grid-cols-[0.78fr_1.22fr] lg:items-start">
            <aside class="linkqr-card linkqr-premium rounded-[1.7rem] p-6 sm:p-8 lg:sticky lg:top-28">
                <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <i class="fa-light fa-circle-question"></i>
                    {{ __('FAQs') }}
                </span>
                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-[-0.055em] text-slate-950 md:text-5xl">
                    {{ __('Answers for Link Bio and QR campaign teams.') }}
                </h1>
                <p class="mt-5 text-sm leading-7 text-slate-600">
                    {{ __('Search questions about editable QR codes, analytics, domains, teams, billing, and launch workflows.') }}
                </p>

                <form method="GET" action="{{ route('guest.faqs') }}" class="mt-7 space-y-3">
                    <label for="faq-search" class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Search answers') }}</label>
                    <div class="flex gap-2">
                        <input id="faq-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search DNS, QR, analytics...') }}" class="h-12 min-w-0 flex-1 rounded-[var(--theme-input-radius)] border bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">
                        <button type="submit" class="linkqr-button-primary inline-flex h-12 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-bold">
                            <i class="fa-light fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </aside>

            <div x-data="{ openFaq: 0 }" class="space-y-4">
                @forelse ($faqs as $faq)
                    <article class="linkqr-card rounded-[1.25rem] p-5 transition" x-bind:class="openFaq === {{ $loop->index }} ? 'ring-2 ring-blue-500/20' : ''">
                        <button type="button" x-on:click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex w-full items-start justify-between gap-4 text-left">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Question') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h2 class="mt-2 text-xl font-extrabold tracking-[-0.03em] text-slate-950">{{ $faq->titleForLocale() }}</h2>
                            </div>
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.9rem] bg-blue-50 text-blue-700 transition" x-bind:class="openFaq === {{ $loop->index }} ? 'rotate-45' : ''">
                                <i class="fa-light fa-plus"></i>
                            </span>
                        </button>
                        <div class="grid overflow-hidden transition-all duration-300" x-bind:style="openFaq === {{ $loop->index }} ? 'grid-template-rows: 1fr; opacity: 1; margin-top: 1.25rem;' : 'grid-template-rows: 0fr; opacity: 0; margin-top: 0;'">
                            <div class="min-h-0 overflow-hidden rounded-[1rem] bg-slate-50 px-5 py-4 text-sm leading-8 text-slate-600 whitespace-pre-line">
                                {{ $faq->contentPreview(420) !== '' ? $faq->contentPreview(420) : $faq->contentForLocale() }}
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="linkqr-card rounded-[1.5rem] border-dashed px-6 py-14 text-center text-sm text-slate-500">{{ __('No FAQs found.') }}</div>
                @endforelse

                @if ($faqs->hasPages())
                    <div class="pt-4">{{ $faqs->links() }}</div>
                @endif
            </div>
        </div>
    </section>
@endcomponent
