@php($displayBaseUrl = preg_replace('#^https?://#i', '', rtrim(url('/'), '/')))
<div class="hide-on-mobile relative flex flex-1 flex-col justify-center overflow-hidden px-14 py-16" style="background:#fbfaf6;">
    <div class="absolute inset-0 pointer-events-none" style="background-image:linear-gradient(#edf3fb 1px,transparent 1px),linear-gradient(90deg,#edf3fb 1px,transparent 1px);background-size:34px 34px;"></div>
    <div class="relative mx-auto w-full max-w-2xl">
        <a class="mb-14 inline-flex items-center" href="{{ url('') }}">
            <img class="h-10" src="{{ url(get_option('website_logo_brand_dark', 'public/img/logo-brand-dark.png')) }}" alt="">
        </a>

        <div class="max-w-xl">
            <span class="mb-6 inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">
                {{ __("LinkQR workspace") }}
            </span>
            <h2 class="mb-5 font-serif text-6xl leading-[0.92] tracking-[-0.04em] text-[#181714]">
                {{ $name ?? __("Welcome Back") }}
            </h2>
            <p class="mb-10 text-base leading-8 text-[#6d685f]">
                {{ __("Claim a clean username, publish one useful Bio page, share QR campaigns, and read where clicks and scans come from.") }}
            </p>
        </div>

        <div class="rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)]">
            <div class="rounded-[1.35rem] bg-[#d8f2fb] p-5" style="background-image:linear-gradient(#b8e1ef 1px,transparent 1px),linear-gradient(90deg,#b8e1ef 1px,transparent 1px);background-size:28px 28px;">
                <div class="rounded-[1.15rem] border-4 border-[#5f8dff] bg-[#fffdf8] p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ __("Public profile") }}</p>
                    <p class="mt-1 font-mono text-sm font-bold text-[#181714]">{{ $displayBaseUrl }}/yourname</p>
                    <div class="mt-6 grid gap-3">
                        @foreach ([__("Username check"), __("Bio page builder"), __("QR and city analytics")] as $item)
                            <div class="rounded-xl border border-[#e5dfd2] bg-white px-4 py-3 text-sm font-bold text-[#57534b]">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
