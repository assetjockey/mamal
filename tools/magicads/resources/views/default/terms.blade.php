{{-- Terms of Service — standalone legal page. --}}
@extends('layouts.frontend')

@section('metadata')
    <title>{{ __('Terms of Service') }} — {{ $generalSettings?->site_name ?? config('app.name') }}</title>
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
                {{ __('Terms of Service') }}
            </h1>
            <p class="mt-4 text-[15px] text-white/60">
                {{ __('Please read these terms carefully before using our platform. By accessing the Service, you agree to be bound by these terms.') }}
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
        @if (filled($pageSettings?->terms_content))
            {{-- Admin-managed content --}}
            @if ($pageSettings->terms_updated_at)
                <p class="text-[13px] text-black/50">{{ __('Last updated:') }} {{ $pageSettings->terms_updated_at->format('F Y') }}</p>
            @endif
            <div class="pg-prose mt-10">
                {!! $pageSettings->terms_content !!}
            </div>
        @else
        {{-- Built-in default copy (shown until an admin customizes the page) --}}
        <p class="text-[13px] text-black/50">{{ __('Last updated: May 2026') }}</p>

        {{-- 1. Introduction --}}
        <div class="mt-10">
            <h2 class="text-xl font-bold text-black">{{ __('1. Introduction') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('These Terms of Service ("Terms") govern your access to and use of the services, website, and applications (collectively, the "Service") provided by :site_name ("we," "our," or "us"). By creating an account or using the Service, you acknowledge that you have read, understood, and agree to be bound by these Terms. If you do not agree to these Terms, you must not access or use the Service.', ['site_name' => $generalSettings?->site_name ?? config('app.name')]) }}
            </p>
        </div>

        {{-- 2. Description of Service --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('2. Description of Service') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('The Service provides an AI-powered platform for generating advertising creative assets, including images, video, and copy. We reserve the right to modify, suspend, or discontinue any aspect of the Service at any time, with or without notice. We shall not be liable to you or any third party for any modification, suspension, or discontinuation of the Service.') }}
            </p>
        </div>

        {{-- 3. Account Registration and Responsibilities --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('3. Account Registration and Responsibilities') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('To access certain features of the Service, you must register for an account. When registering, you agree to:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Provide accurate, current, and complete information during registration') }}</li>
                <li>{{ __('Maintain and promptly update your account information to keep it accurate') }}</li>
                <li>{{ __('Maintain the security and confidentiality of your login credentials') }}</li>
                <li>{{ __('Accept responsibility for all activities that occur under your account') }}</li>
                <li>{{ __('Notify us immediately of any unauthorized use of your account') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('You must be at least 16 years of age to create an account. We reserve the right to suspend or terminate accounts that violate these Terms or that we reasonably believe are being used fraudulently.') }}
            </p>
        </div>

        {{-- 4. Subscription and Billing --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('4. Subscription and Billing') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Certain features of the Service require a paid subscription. By subscribing to a paid plan, you agree to the following:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Subscription fees are billed in advance on a recurring basis (monthly or annually, depending on your selected plan)') }}</li>
                <li>{{ __('Your subscription will automatically renew unless you cancel before the end of the current billing period') }}</li>
                <li>{{ __('We reserve the right to change subscription pricing with 30 days advance notice') }}</li>
                <li>{{ __('Refunds are handled in accordance with our refund policy and applicable law') }}</li>
                <li>{{ __('Failure to pay may result in suspension or termination of your access to paid features') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('All fees are exclusive of applicable taxes, which will be added where required by law. You are responsible for providing valid and up-to-date payment information.') }}
            </p>
        </div>

        {{-- 5. Intellectual Property --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('5. Intellectual Property') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('The Service and its original content (excluding user-generated content), features, and functionality are and will remain the exclusive property of :site_name and its licensors. The Service is protected by copyright, trademark, and other intellectual property laws. Our trademarks and trade dress may not be used in connection with any product or service without our prior written consent.', ['site_name' => $generalSettings?->site_name ?? config('app.name')]) }}
            </p>
        </div>

        {{-- 6. User Content and Licenses --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('6. User Content and Licenses') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('You retain ownership of any content you upload, submit, or create through the Service ("User Content"). By submitting User Content, you grant us a non-exclusive, worldwide, royalty-free license to use, reproduce, and process your content solely for the purpose of providing and improving the Service.') }}
            </p>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('AI-generated outputs created through the Service are licensed to you for commercial and personal use in accordance with your subscription plan. You are responsible for ensuring that your use of generated content complies with applicable laws and does not infringe on third-party rights.') }}
            </p>
        </div>

        {{-- 7. Acceptable Use Policy --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('7. Acceptable Use Policy') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('You agree not to use the Service to:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Violate any applicable local, national, or international law or regulation') }}</li>
                <li>{{ __('Generate content that is defamatory, obscene, hateful, or promotes violence') }}</li>
                <li>{{ __('Infringe upon the intellectual property rights of others') }}</li>
                <li>{{ __('Transmit malware, viruses, or any code of a destructive nature') }}</li>
                <li>{{ __('Attempt to gain unauthorized access to the Service or its related systems') }}</li>
                <li>{{ __('Use the Service to send unsolicited communications (spam)') }}</li>
                <li>{{ __('Impersonate any person or entity, or misrepresent your affiliation') }}</li>
                <li>{{ __('Interfere with or disrupt the integrity or performance of the Service') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We reserve the right to investigate and take appropriate action against anyone who violates this provision, including removing content, suspending accounts, and reporting to law enforcement.') }}
            </p>
        </div>

        {{-- 8. Limitation of Liability --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('8. Limitation of Liability') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('To the maximum extent permitted by applicable law, in no event shall :site_name, its directors, employees, partners, agents, suppliers, or affiliates be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from:', ['site_name' => $generalSettings?->site_name ?? config('app.name')]) }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Your access to or use of (or inability to access or use) the Service') }}</li>
                <li>{{ __('Any conduct or content of any third party on the Service') }}</li>
                <li>{{ __('Any content obtained from the Service') }}</li>
                <li>{{ __('Unauthorized access, use, or alteration of your transmissions or content') }}</li>
            </ul>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('Our total liability to you for all claims arising from or related to the Service shall not exceed the amount you paid us in the twelve (12) months preceding the claim.') }}
            </p>
        </div>

        {{-- 9. Disclaimer of Warranties --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('9. Disclaimer of Warranties') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('The Service is provided on an "AS IS" and "AS AVAILABLE" basis without warranties of any kind, whether express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, non-infringement, and course of dealing. We do not warrant that the Service will be uninterrupted, timely, secure, or error-free, or that any defects will be corrected.') }}
            </p>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('AI-generated content may contain inaccuracies or errors. You acknowledge that you are solely responsible for reviewing and validating any content generated through the Service before use.') }}
            </p>
        </div>

        {{-- 10. Termination --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('10. Termination') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We may terminate or suspend your account and access to the Service immediately, without prior notice or liability, for any reason, including without limitation if you breach these Terms. Upon termination, your right to use the Service will immediately cease.') }}
            </p>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('You may terminate your account at any time by contacting us or using the account deletion feature in your settings. All provisions of these Terms which by their nature should survive termination shall survive, including ownership provisions, warranty disclaimers, indemnity, and limitations of liability.') }}
            </p>
        </div>

        {{-- 11. Governing Law --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('11. Governing Law') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which our company is registered, without regard to its conflict of law provisions. Any disputes arising from or relating to these Terms or the Service shall be resolved exclusively in the courts of that jurisdiction. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.') }}
            </p>
        </div>

        {{-- 12. Changes to Terms --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('12. Changes to Terms') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('We reserve the right to modify or replace these Terms at any time at our sole discretion. When we make material changes, we will provide notice through the Service or by other means. Your continued use of the Service after such changes constitutes your acceptance of the new Terms. If you do not agree to the new Terms, you must stop using the Service.') }}
            </p>
        </div>

        {{-- 13. Contact Us --}}
        <div class="border-t border-[var(--l-hairline)] pt-8 mt-8">
            <h2 class="text-xl font-bold text-black">{{ __('13. Contact Us') }}</h2>
            <p class="mt-4 text-[14px] leading-relaxed text-black/70">
                {{ __('If you have any questions about these Terms of Service, please contact us:') }}
            </p>
            <ul class="mt-3 list-disc pl-5 space-y-1.5 text-[14px] leading-relaxed text-black/70">
                <li>{{ __('Email:') }} {{ $generalSettings?->contact_email ?? 'legal@example.com' }}</li>
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
