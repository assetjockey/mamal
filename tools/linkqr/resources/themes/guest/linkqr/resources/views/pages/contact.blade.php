@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $companyName = trim((string) $options->get('contact_company_name', config('app.name', 'LinkQR Pro')));
    $companyWebsite = trim((string) $options->get('contact_company_website', 'https://yourcompany.com'));
    $contactEmail = trim((string) $options->get('contact_email', 'support@yourcompany.com'));
    $contactPhone = trim((string) $options->get('contact_phone_number', '+1 234 567 890'));
    $workingHours = trim((string) $options->get('contact_working_hours', 'Mon - Fri: 09:00 AM - 06:00 PM'));
    $contactLocation = trim((string) $options->get('contact_location', '123 Main Street, City, Country'));
    $contactCards = [
        ['icon' => 'fa-light fa-envelope', 'label' => __('Email'), 'value' => $contactEmail, 'href' => 'mailto:'.$contactEmail],
        ['icon' => 'fa-light fa-phone', 'label' => __('Phone'), 'value' => $contactPhone, 'href' => 'tel:'.preg_replace('/\s+/', '', $contactPhone)],
        ['icon' => 'fa-light fa-globe', 'label' => __('Website'), 'value' => $companyWebsite, 'href' => $companyWebsite],
        ['icon' => 'fa-light fa-clock', 'label' => __('Working hours'), 'value' => $workingHours],
        ['icon' => 'fa-light fa-location-dot', 'label' => __('Address'), 'value' => $contactLocation, 'span' => 'lg:col-span-2'],
    ];
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="linkqr-shell linkqr-section pt-10">
        <div class="grid gap-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <i class="fa-light fa-message-lines"></i>
                    {{ __('Contact') }}
                </span>
                <h1 class="mt-6 text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">
                    {{ __('Talk to the team behind your QR growth workspace.') }}
                </h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600">
                    {{ __('Reach out for onboarding, pricing, custom domains, agency workflow, or help launching a production-ready LinkQR setup.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="mailto:{{ $contactEmail }}" class="linkqr-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-6 py-4 text-sm font-bold">
                        <i class="fa-light fa-envelope mr-2"></i>
                        {{ __('Email support') }}
                    </a>
                    <a href="{{ route('guest.pricing') }}" class="linkqr-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-6 py-4 text-sm font-bold">
                        {{ __('View pricing') }}
                    </a>
                </div>
            </div>

            <div class="linkqr-card overflow-hidden rounded-[1.7rem]">
                <div class="relative h-72">
                    @include(theme_view('partials.visual-card', 'guest'), [
                        'type' => 'workspace',
                        'icon' => 'fa-light fa-message-lines',
                    ])
                    <div class="absolute bottom-5 left-5 right-5 z-10">
                        <span class="linkqr-glass-badge rounded-full px-4 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-700">{{ $companyName }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-4 lg:grid-cols-3">
            @foreach ($contactCards as $item)
                @php $isLink = filled($item['href'] ?? null); @endphp
                @if ($isLink)
                    <a href="{{ $item['href'] }}" @if (($item['label'] ?? '') === __('Website')) target="_blank" rel="noopener noreferrer" @endif class="linkqr-card linkqr-hover-lift rounded-[1.25rem] p-5 {{ $item['span'] ?? '' }}">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem] bg-blue-50 text-blue-700">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $item['label'] }}</p>
                        <p class="mt-2 break-words text-lg font-extrabold tracking-[-0.03em] text-slate-950">{{ $item['value'] }}</p>
                    </a>
                @else
                    <div class="linkqr-card linkqr-hover-lift rounded-[1.25rem] p-5 {{ $item['span'] ?? '' }}">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem] bg-blue-50 text-blue-700">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $item['label'] }}</p>
                        <p class="mt-2 break-words text-lg font-extrabold tracking-[-0.03em] text-slate-950">{{ $item['value'] }}</p>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            @foreach ([['fa-light fa-compass', __('Onboarding and setup'), __('Configure Bio pages, short links, QR campaigns, domains, UTM presets, and workspace defaults correctly from day one.')], ['fa-light fa-chart-line-up', __('Pricing and scale'), __('Map the right plan to clicks, scan volume, team seats, client workspaces, and campaign growth.')], ['fa-light fa-diagram-project', __('Operational workflows'), __('Plan agency roles, activity logs, approvals, and health alerts before rollout.')]] as $item)
                <article class="linkqr-card rounded-[1.25rem] p-5">
                    <i class="{{ $item[0] }} text-2xl text-blue-700"></i>
                    <h2 class="mt-4 text-xl font-extrabold tracking-[-0.035em] text-slate-950">{{ $item[1] }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $item[2] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endcomponent
