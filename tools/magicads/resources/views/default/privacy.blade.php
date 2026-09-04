{{-- Privacy Policy — standalone legal page. --}}
@extends('layouts.frontend')

@section('metadata')
    <title>{{ __('Privacy Policy') }} — {{ $generalSettings?->site_name ?? config('app.name') }}</title>
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
                {{ __('Legal') }}
            </span>
            <h1 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-white sm:text-5xl lg:text-6xl">
                {{ __('Privacy Policy') }}
            </h1>
            <p class="mt-4 text-[15px] text-white/60">
                {{ __('Your privacy matters to us. This policy explains how we collect, use, and protect your personal information.') }}
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
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        @if (filled($pageSettings?->privacy_content))
            {{-- Admin-managed content --}}
            @if ($pageSettings->privacy_updated_at)
                <p class="text-[13px] text-black/50">{{ __('Last updated:') }} {{ $pageSettings->privacy_updated_at->format('F Y') }}</p>
            @endif
            <div class="pg-prose mt-10">
                {!! $pageSettings->privacy_content !!}
            </div>
        @else
        {{-- Built-in default copy (shown until an admin customizes the page) --}}
        <p class="text-[13px] text-black/50">{{ __('Last updated: May 2026') }}</p>

        {{-- 1. Introduction --}}
        <div class="mt-10">
            <h2 class="text-xl font-bold text-black">{{ __('1. Introduction') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Welcome to :site_name ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy describes how we collect, use, store, and share your information when you use our platform, website, and related services (collectively, the "Service"). By accessing or using the Service, you agree to the terms of this Privacy Policy. If you do not agree, please discontinue use of the Service immediately.', ['site_name' => $generalSettings?->site_name ?? config('app.name')]) }}
            </p>
        </div>

        {{-- 2. Information We Collect --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('2. Information We Collect') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We collect information in several ways depending on how you interact with our Service:') }}
            </p>

            <h3 class="mt-6 text-[15px] font-semibold text-black">{{ __('Personal Data') }}</h3>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Name, email address, and contact details provided during account registration') }}</li>
                <li>{{ __('Billing information such as payment method details and billing address') }}</li>
                <li>{{ __('Profile information including avatar, company name, and preferences') }}</li>
                <li>{{ __('Communications you send to us, including support requests and feedback') }}</li>
            </ul>

            <h3 class="mt-6 text-[15px] font-semibold text-black">{{ __('Usage Data') }}</h3>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Device information (browser type, operating system, device identifiers)') }}</li>
                <li>{{ __('Log data such as IP address, access times, pages viewed, and referring URLs') }}</li>
                <li>{{ __('Feature usage patterns, session duration, and interaction data') }}</li>
                <li>{{ __('Performance metrics and error reports generated during your use of the Service') }}</li>
            </ul>

            <h3 class="mt-6 text-[15px] font-semibold text-black">{{ __('Cookies & Tracking Technologies') }}</h3>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Essential cookies required for authentication and security') }}</li>
                <li>{{ __('Analytics cookies that help us understand how visitors use the Service') }}</li>
                <li>{{ __('Preference cookies that remember your settings and choices') }}</li>
                <li>{{ __('Marketing cookies used to deliver relevant advertisements (only with your consent)') }}</li>
            </ul>
        </div>

        {{-- 3. How We Use Your Information --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('3. How We Use Your Information') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We use the information we collect for the following purposes:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('To provide, operate, and maintain the Service') }}</li>
                <li>{{ __('To process transactions and send related billing information') }}</li>
                <li>{{ __('To communicate with you, including responding to inquiries and sending service updates') }}</li>
                <li>{{ __('To personalize your experience and deliver content relevant to your interests') }}</li>
                <li>{{ __('To monitor and analyze usage trends to improve the Service') }}</li>
                <li>{{ __('To detect, prevent, and address fraud, abuse, and technical issues') }}</li>
                <li>{{ __('To comply with legal obligations and enforce our terms') }}</li>
            </ul>
        </div>

        {{-- 4. Data Sharing and Third Parties --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('4. Data Sharing and Third Parties') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We do not sell your personal information. We may share your data with third parties only in the following circumstances:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Service providers who assist us in operating the platform (hosting, payment processing, analytics)') }}</li>
                <li>{{ __('Professional advisors such as lawyers, auditors, and insurers where necessary') }}</li>
                <li>{{ __('Law enforcement or regulatory authorities when required by applicable law') }}</li>
                <li>{{ __('In connection with a merger, acquisition, or sale of assets, with appropriate notice to you') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('All third-party service providers are contractually obligated to handle your data in accordance with this Privacy Policy and applicable data protection laws.') }}
            </p>
        </div>

        {{-- 5. Data Retention --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('5. Data Retention') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We retain your personal information only for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When determining the appropriate retention period, we consider the amount, nature, and sensitivity of the data, the potential risk of harm from unauthorized use or disclosure, and applicable legal requirements.') }}
            </p>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('When your data is no longer needed, we will securely delete or anonymize it. If deletion is not immediately possible (for example, because the data is stored in backup archives), we will securely isolate it until deletion is feasible.') }}
            </p>
        </div>

        {{-- 6. Your Rights --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('6. Your Rights') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Depending on your jurisdiction, you may have the following rights regarding your personal data:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Right of access — request a copy of the personal data we hold about you') }}</li>
                <li>{{ __('Right to rectification — request correction of inaccurate or incomplete data') }}</li>
                <li>{{ __('Right to erasure — request deletion of your personal data under certain conditions') }}</li>
                <li>{{ __('Right to data portability — receive your data in a structured, machine-readable format') }}</li>
                <li>{{ __('Right to restrict processing — request that we limit how we use your data') }}</li>
                <li>{{ __('Right to object — object to processing based on legitimate interests or direct marketing') }}</li>
                <li>{{ __('Right to withdraw consent — where processing is based on consent, withdraw it at any time') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('To exercise any of these rights, please contact us using the details provided at the end of this policy. We will respond to your request within 30 days.') }}
            </p>
        </div>

        {{-- 7. Cookies and Tracking --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('7. Cookies and Tracking') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Our Service uses cookies and similar tracking technologies to distinguish you from other users. This helps us provide you with a good experience and allows us to improve the Service. You can set your browser to refuse all or some cookies, or to alert you when websites set or access cookies. If you disable or refuse cookies, some parts of the Service may become inaccessible or not function properly.') }}
            </p>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We may also use web beacons, pixel tags, and other tracking technologies to collect information about your browsing activities. You can manage your cookie preferences through our cookie consent banner or your browser settings at any time.') }}
            </p>
        </div>

        {{-- 8. Children's Privacy --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __("8. Children's Privacy") }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Our Service is not directed to individuals under the age of 16. We do not knowingly collect personal information from children. If we become aware that we have collected personal data from a child without verification of parental consent, we will take steps to remove that information from our servers. If you believe we may have collected information from a child, please contact us immediately.') }}
            </p>
        </div>

        {{-- 9. Changes to This Policy --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('9. Changes to This Policy') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors. When we make material changes, we will notify you by posting the updated policy on this page and updating the "Last updated" date at the top. We encourage you to review this Privacy Policy periodically to stay informed about how we are protecting your information.') }}
            </p>
        </div>

        {{-- 10. Contact Us --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('10. Contact Us') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('If you have any questions about this Privacy Policy, wish to exercise your data rights, or have concerns about how we handle your personal information, please contact us:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Email:') }} {{ $generalSettings?->contact_email ?? 'privacy@example.com' }}</li>
                <li>{{ __('Through our contact page:') }} <a href="{{ route('contact') }}" class="text-[#4F46E5] hover:underline">{{ route('contact') }}</a></li>
            </ul>
        </div>
        @endif
    </div>
</section>
@endsection

@section('css')
    @include('frontend.partials.legal-prose')
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection
