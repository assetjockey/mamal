{{-- Contact Us — standalone page with company info + contact form. --}}
@extends('layouts.frontend')

@section('metadata')
    <title>{{ __('Contact Us') }} — {{ $generalSettings?->site_name ?? config('app.name') }}</title>
@endsection

@section('menu')
    @include('frontend.menu.section')
@endsection

@section('content')
<section class="relative overflow-hidden bg-black py-20 text-white sm:py-28">
    {{-- Subtle indigo bloom --}}
    <div aria-hidden="true" class="pointer-events-none absolute -right-40 -top-40 h-[500px] w-[500px] rounded-full opacity-40 blur-3xl"
         style="background: radial-gradient(circle, rgba(79, 70, 229, 0.25), transparent 70%);"></div>
    <div aria-hidden="true" class="l-grain"></div>

    {{-- Architectural accent lines — same as homepage hero --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute top-[22%] left-0 right-0 h-px"
             style="background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.12) 30%, rgba(79,70,229,0.2) 55%, transparent 90%);"></div>
        <div class="absolute top-[72%] left-0 right-0 h-px"
             style="background: linear-gradient(90deg, transparent 10%, rgba(79,70,229,0.16) 35%, rgba(255,255,255,0.10) 60%, transparent 100%);"></div>
        <div class="absolute top-0 bottom-0 left-[50%] w-px hidden lg:block"
             style="background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,0.06) 25%, rgba(255,255,255,0.06) 75%, transparent 100%);"></div>
    </div>

    {{-- Fine grid texture --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.04]"
         style="background-image:
                linear-gradient(rgba(255,255,255,0.9) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.9) 1px, transparent 1px);
                background-size: 36px 36px;
                mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, black 30%, transparent 80%);
                -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, black 30%, transparent 80%);"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <span class="l-chip l-chip--indigo !bg-[#4F46E5]/20 !text-white !border-[#4F46E5]/30">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('Contact') }}
            </span>
            <h1 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-white sm:text-5xl lg:text-6xl">
                {{ __("Let's talk.") }}
            </h1>
            <p class="mt-4 text-[15px] text-white/60">
                {{ __("Have a question, need a demo, or want to discuss enterprise pricing? We'd love to hear from you.") }}
            </p>
        </div>
    </div>
</section>

{{-- Curve transition --}}
<div aria-hidden="true" class="relative h-10 sm:h-12 overflow-hidden bg-white">
    <div class="absolute inset-x-0 -top-10 sm:-top-12 leading-[0]">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="block h-10 w-full sm:h-12">
            <path d="M0,0 Q 720,80 1440,0 L1440,60 L0,60 Z" fill="#FFFFFF"/>
        </svg>
    </div>
</div>

<section class="relative bg-white py-16 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-[1fr_1.2fr] lg:gap-20">

            {{-- Left: Company info --}}
            <div>
                <h2 class="l-display text-2xl font-bold tracking-tight text-black sm:text-3xl">
                    {{ __('Get in touch') }}
                </h2>
                <p class="mt-4 text-[15px] leading-relaxed text-black/60">
                    {{ __("Whether you're exploring AI ad generation for the first time or scaling an existing pipeline, our team is here to help.") }}
                </p>

                <div class="mt-10 space-y-6">
                    {{-- Email --}}
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--l-bg-2)] text-black">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>
                            </svg>
                        </span>
                        <div>
                            <div class="text-[13px] font-semibold text-black">{{ __('Email') }}</div>
                            <a href="mailto:{{ $generalSettings?->contact_email ?? 'hello@example.com' }}"
                               class="mt-0.5 text-[14px] text-[#4F46E5] transition-colors hover:text-[#3E36D9]">
                                {{ $generalSettings?->contact_email ?? 'hello@example.com' }}
                            </a>
                        </div>
                    </div>

                    {{-- Response time --}}
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--l-bg-2)] text-black">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
                            </svg>
                        </span>
                        <div>
                            <div class="text-[13px] font-semibold text-black">{{ __('Response time') }}</div>
                            <p class="mt-0.5 text-[14px] text-black/60">{{ __('We typically respond within 2–4 business hours.') }}</p>
                        </div>
                    </div>

                    {{-- Office --}}
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--l-bg-2)] text-black">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                            </svg>
                        </span>
                        <div>
                            <div class="text-[13px] font-semibold text-black">{{ __('Office') }}</div>
                            <p class="mt-0.5 text-[14px] text-black/60">{{ __('Remote-first team, serving customers worldwide.') }}</p>
                        </div>
                    </div>

                    {{-- Social --}}
                    <div class="flex items-start gap-4">
                        <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--l-bg-2)] text-black">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
                            </svg>
                        </span>
                        <div>
                            <div class="text-[13px] font-semibold text-black">{{ __('Follow us') }}</div>
                            <p class="mt-0.5 text-[14px] text-black/60">{{ __('LinkedIn · Twitter · YouTube') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Trust badges --}}
                <div class="mt-10 flex items-center gap-6 border-t border-[var(--l-hairline)] pt-8">
                    <div class="text-center">
                        <div class="l-display text-2xl font-bold text-black">1,200+</div>
                        <div class="l-mono text-[10px] uppercase tracking-wider text-black/50">{{ __('Teams') }}</div>
                    </div>
                    <div class="h-8 w-px bg-[var(--l-hairline)]"></div>
                    <div class="text-center">
                        <div class="l-display text-2xl font-bold text-black">4.9★</div>
                        <div class="l-mono text-[10px] uppercase tracking-wider text-black/50">{{ __('Rating') }}</div>
                    </div>
                    <div class="h-8 w-px bg-[var(--l-hairline)]"></div>
                    <div class="text-center">
                        <div class="l-display text-2xl font-bold text-black">&lt;4h</div>
                        <div class="l-mono text-[10px] uppercase tracking-wider text-black/50">{{ __('Reply') }}</div>
                    </div>
                </div>
            </div>

            {{-- Right: Contact form --}}
            <div class="l-card p-8 sm:p-10">
                <h3 class="text-lg font-bold text-black">{{ __('Send us a message') }}</h3>
                <p class="mt-2 text-[13px] text-black/60">{{ __("Fill out the form below and we'll get back to you shortly.") }}</p>

                @if (session('contact_success'))
                    <div class="mt-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        <span>{{ session('contact_success') }}</span>
                    </div>
                @endif

                @if (session('contact_error'))
                    <div class="mt-6 flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-800">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                        </svg>
                        <span>{{ session('contact_error') }}</span>
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- First name --}}
                        <div>
                            <label for="contact-first-name" class="block text-[12px] font-semibold text-black">{{ __('First name') }}</label>
                            <input type="text" id="contact-first-name" name="first_name" required value="{{ old('first_name') }}"
                                   class="mt-1.5 block w-full rounded-lg border bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 @error('first_name') border-rose-400 @else border-[var(--l-border)] @enderror"
                                   placeholder="{{ __('John') }}">
                            @error('first_name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Last name --}}
                        <div>
                            <label for="contact-last-name" class="block text-[12px] font-semibold text-black">{{ __('Last name') }}</label>
                            <input type="text" id="contact-last-name" name="last_name" required value="{{ old('last_name') }}"
                                   class="mt-1.5 block w-full rounded-lg border bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 @error('last_name') border-rose-400 @else border-[var(--l-border)] @enderror"
                                   placeholder="{{ __('Doe') }}">
                            @error('last_name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="contact-email" class="block text-[12px] font-semibold text-black">{{ __('Work email') }}</label>
                        <input type="email" id="contact-email" name="email" required value="{{ old('email') }}"
                               class="mt-1.5 block w-full rounded-lg border bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 @error('email') border-rose-400 @else border-[var(--l-border)] @enderror"
                               placeholder="{{ __('john@company.com') }}">
                        @error('email')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Company --}}
                    <div>
                        <label for="contact-company" class="block text-[12px] font-semibold text-black">{{ __('Company') }}</label>
                        <input type="text" id="contact-company" name="company" value="{{ old('company') }}"
                               class="mt-1.5 block w-full rounded-lg border bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 @error('company') border-rose-400 @else border-[var(--l-border)] @enderror"
                               placeholder="{{ __('Acme Corp') }}">
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label for="contact-subject" class="block text-[12px] font-semibold text-black">{{ __('Subject') }}</label>
                        <select id="contact-subject" name="subject"
                                class="mt-1.5 block w-full rounded-lg border border-[var(--l-border)] bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">
                            <option value="general" @selected(old('subject') === 'general')>{{ __('General inquiry') }}</option>
                            <option value="demo" @selected(old('subject') === 'demo')>{{ __('Request a demo') }}</option>
                            <option value="enterprise" @selected(old('subject') === 'enterprise')>{{ __('Enterprise pricing') }}</option>
                            <option value="support" @selected(old('subject') === 'support')>{{ __('Technical support') }}</option>
                            <option value="partnership" @selected(old('subject') === 'partnership')>{{ __('Partnership') }}</option>
                        </select>
                        @error('subject')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="contact-message" class="block text-[12px] font-semibold text-black">{{ __('Message') }}</label>
                        <textarea id="contact-message" name="message" rows="4" required
                                  class="mt-1.5 block w-full resize-none rounded-lg border bg-[var(--l-bg-2)] px-3.5 py-2.5 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20 @error('message') border-rose-400 @else border-[var(--l-border)] @enderror"
                                  placeholder="{{ __('Tell us how we can help...') }}">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-black px-6 py-3.5 text-sm font-semibold text-white transition-all hover:bg-[#1F1F1F] hover:scale-[1.01] sm:w-auto">
                        <span>{{ __('Send message') }}</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                        </svg>
                    </button>

                    <p class="text-[11px] text-black/50">
                        {{ __('By submitting this form, you agree to our privacy policy. We will never share your information with third parties.') }}
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection
