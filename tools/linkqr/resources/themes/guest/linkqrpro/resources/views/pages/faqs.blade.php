@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="px-5 py-14 sm:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <aside class="lg:sticky lg:top-24">
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('FAQs') }}</span>
                    <h1 class="mt-6 font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ __('Answers before you launch.') }}</h1>
                    <p class="mt-5 max-w-md text-sm leading-7 text-[#6d685f]">{{ __('Find quick answers about Bio pages, short links, QR campaigns, analytics, domains, teams, and billing.') }}</p>

                    <form method="GET" action="{{ route('guest.faqs') }}" class="mt-8 rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-3 shadow-[0_20px_60px_-52px_rgba(24,23,20,.45)]">
                        <label for="faq-search" class="sr-only">{{ __('Search answers') }}</label>
                        <div class="flex gap-2">
                            <input id="faq-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search questions...') }}" class="h-12 min-w-0 flex-1 rounded-xl border border-[#e5dfd2] bg-[#fbfaf6] px-4 text-sm font-semibold text-[#181714] outline-none focus:border-[#5f8dff]">
                            <button type="submit" class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-[#181714] text-white">
                                <i class="fa-light fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>
                </aside>

                <div x-data="{ openFaq: 0 }" class="space-y-3">
                    @forelse ($faqs as $faq)
                        <article class="rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] shadow-[0_18px_50px_-45px_rgba(24,23,20,.42)]">
                            <button type="button" x-on:click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex w-full items-start justify-between gap-4 px-5 py-5 text-left sm:px-6">
                                <div>
                                    <p class="font-mono text-xs font-bold text-[#8a867d]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                    <h2 class="mt-2 font-serif text-2xl leading-tight text-[#181714]">{{ $faq->titleForLocale() }}</h2>
                                </div>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#cfeefd] text-[#181714] transition" x-bind:class="openFaq === {{ $loop->index }} ? 'rotate-45' : ''">
                                    <i class="fa-light fa-plus"></i>
                                </span>
                            </button>
                            <div class="grid overflow-hidden transition-all duration-300" x-bind:style="openFaq === {{ $loop->index }} ? 'grid-template-rows: 1fr; opacity: 1;' : 'grid-template-rows: 0fr; opacity: 0;'">
                                <div class="min-h-0 overflow-hidden px-5 pb-5 sm:px-6">
                                    <div class="rounded-xl bg-[#fbfaf6] px-5 py-4 text-sm leading-8 text-[#6d685f] whitespace-pre-line">
                                        {{ $faq->contentPreview(520) !== '' ? $faq->contentPreview(520) : $faq->contentForLocale() }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-[#ded7ca] bg-[#fffdf8] px-6 py-14 text-center text-sm text-[#6d685f]">{{ __('No FAQs found.') }}</div>
                    @endforelse

                    @if ($faqs->hasPages())
                        <div class="pt-4">{{ $faqs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endcomponent
