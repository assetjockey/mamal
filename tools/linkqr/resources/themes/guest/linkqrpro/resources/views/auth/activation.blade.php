<div class="flex min-h-screen items-center justify-center px-6 py-16" style="background:#fbfaf6;background-image:linear-gradient(#edf3fb 1px,transparent 1px),linear-gradient(90deg,#edf3fb 1px,transparent 1px);background-size:34px 34px;">
    <div class="absolute inset-0 pointer-events-none" style="background:
        radial-gradient(circle at 25% 30%, rgba(79,70,229,.08) 0%, rgba(79,70,229,0) 22%),
        radial-gradient(circle at 78% 75%, rgba(59,130,246,.08) 0%, rgba(59,130,246,0) 24%);"></div>
    <div class="relative w-full max-w-lg rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-8 text-center shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] md:p-12">
        <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-full {{ $status ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
            <i class="fas {{ $status ? 'fa-check-circle' : 'fa-times-circle' }} text-4xl"></i>
        </div>
        <span class="mb-4 inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">
            {{ $status ? __('Account activated') : __('Activation issue') }}
        </span>
        <h2 class="mb-4 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">
            {{ $status ? __('Activation Successful!') : __('Activation Failed') }}
        </h2>
        <p class="max-w-lg mx-auto text-lg leading-8 text-gray-600">
            {{ $message ?? ($status
                ? __('Your account has been activated. You can now login.')
                : __('The activation link is invalid, expired or your account was already activated.')) }}
        </p>
        <div class="mt-8">
        <a href="{{ url('auth/login') }}" class="inline-block rounded-xl bg-[#181714] px-9 py-4 text-center text-lg font-semibold text-white transition hover:-translate-y-0.5" style="min-width:220px;">
                <i class="fa fa-arrow-left mr-2"></i>
                {{ __("Back to Login") }}
            </a>
        </div>
    </div>
</div>
