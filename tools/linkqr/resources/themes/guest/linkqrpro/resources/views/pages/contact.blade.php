@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $companyName = trim((string) $options->get('contact_company_name', config('app.name', 'LinkQR Pro')));
    $companyWebsite = trim((string) $options->get('contact_company_website', 'https://yourcompany.com'));
    $contactEmail = trim((string) $options->get('contact_email', 'support@yourcompany.com'));
    $contactPhone = trim((string) $options->get('contact_phone_number', '+1 234 567 890'));
    $workingHours = trim((string) $options->get('contact_working_hours', 'Mon - Fri: 09:00 AM - 06:00 PM'));
    $contactLocation = trim((string) $options->get('contact_location', '123 Main Street, City, Country'));
    $contactCards = [
        ['icon' => 'fa-light fa-envelope', 'label' => __('Email'), 'value' => $contactEmail, 'href' => 'mailto:'.$contactEmail, 'color' => '#cfeefd'],
        ['icon' => 'fa-light fa-phone', 'label' => __('Phone'), 'value' => $contactPhone, 'href' => 'tel:'.preg_replace('/\s+/', '', $contactPhone), 'color' => '#cef8dc'],
        ['icon' => 'fa-light fa-globe', 'label' => __('Website'), 'value' => $companyWebsite, 'href' => $companyWebsite, 'color' => '#fff3d8'],
        ['icon' => 'fa-light fa-clock', 'label' => __('Working hours'), 'value' => $workingHours, 'color' => '#f7d1e6'],
        ['icon' => 'fa-light fa-location-dot', 'label' => __('Address'), 'value' => $contactLocation, 'span' => 'lg:col-span-2', 'color' => '#e4ddff'],
    ];
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="px-5 py-14 sm:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div>
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Contact') }}</span>
                    <h1 class="mt-6 font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ __('Talk to the team behind your launch.') }}</h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-[#6d685f]">{{ __('Get help with onboarding, pricing, short links, custom domains, QR campaigns, agency workflows, or production rollout.') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="mailto:{{ $contactEmail }}" class="inline-flex min-h-12 items-center rounded-xl bg-[#181714] px-5 text-sm font-bold text-white">{{ __('Email support') }}</a>
                        <a href="{{ route('guest.pricing') }}" class="inline-flex min-h-12 items-center rounded-xl border border-[#d8d3c7] bg-white/70 px-5 text-sm font-bold text-[#181714]">{{ __('View pricing') }}</a>
                    </div>
                </div>

                <div class="linkqrpro-blue-paper rounded-[1.75rem] p-6 shadow-[0_28px_85px_-64px_rgba(24,23,20,.48)]">
                    <div class="rounded-[1.35rem] border-4 border-[#5f8dff] bg-[#fffdf8] p-6">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ $companyName }}</p>
                        <h2 class="mt-4 font-serif text-4xl leading-tight text-[#181714]">{{ __('Support for Bio pages, short links, QR, and analytics.') }}</h2>
                        <div class="mt-6 grid gap-3">
                            @foreach ([__('Launch setup'), __('Domain and tracking'), __('Plan and workspace advice')] as $item)
                                <div class="rounded-xl border border-[#e5dfd2] bg-white px-4 py-3 text-sm font-bold text-[#57534b]">
                                    <i class="fa-light fa-check mr-2 text-emerald-700"></i>{{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($contactCards as $item)
                    @php $isLink = filled($item['href'] ?? null); @endphp
                    @if ($isLink)
                        <a href="{{ $item['href'] }}" @if (($item['label'] ?? '') === __('Website')) target="_blank" rel="noopener noreferrer" @endif class="rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)] {{ $item['span'] ?? '' }}">
                    @else
                        <div class="rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)] {{ $item['span'] ?? '' }}">
                    @endif
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-[#181714]" style="background: {{ $item['color'] }};">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                        <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ $item['label'] }}</p>
                        <p class="mt-2 break-words text-lg font-bold text-[#181714]">{{ $item['value'] }}</p>
                    @if ($isLink)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endcomponent
