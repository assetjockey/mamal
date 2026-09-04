<section class="relative w-screen min-h-screen flex items-stretch overflow-hidden bg-white overflow-x-hidden">
    @include("partials/login-screen", ["name" => __("Create a New Password")])

    <div class="z-10 flex flex-1 flex-col justify-center px-6 py-16 md:px-8" style="background:#fbfaf6;background-image:linear-gradient(#edf3fb 1px,transparent 1px),linear-gradient(90deg,#edf3fb 1px,transparent 1px);background-size:34px 34px;">
        <div class="absolute inset-0 pointer-events-none" style="background:
            radial-gradient(circle at 25% 30%, rgba(79,70,229,.08) 0%, rgba(79,70,229,0) 22%),
            radial-gradient(circle at 78% 75%, rgba(59,130,246,.08) 0%, rgba(59,130,246,0) 24%);"></div>
        <div class="relative w-full max-w-lg mx-auto">
        <div class="show-on-mobile mb-6 text-center">
            <a class="inline-block" href="{{ url('') }}">
                <img class="h-10" src="{{ url(get_option('website_logo_brand_dark', 'public/img/logo-brand-dark.png')) }}" alt="">
            </a>
        </div>
        <form class="actionForm relative w-full space-y-5 rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-8 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] md:p-12" action="{{ module_url('do_recovery_password') }}" method="POST">
            <div class="mb-10 text-center">
                <span class="mb-5 inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">
                    {{ __("Secure recovery") }}
                </span>
                <h1 class="mb-4 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __("Set a new password") }}</h1>
                <p class="text-base leading-8 text-[#6d685f]">{{ __("Choose a new password for your account and continue back to your workspace.") }}</p>
            </div>

            <input type="hidden" name="token" value="{{ $token ?? request('token') }}">
            <input type="hidden" name="email" value="{{ $email ?? request('email') }}">

            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-2">{{ __("New Password") }}</label>
                <div class="relative">
                    <input id="password" type="password" name="password" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg pr-12 focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Enter new password') }}" required autocomplete="new-password">
                    <button type="button" tabindex="-1" class="absolute top-3 right-4 text-gray-500 hover:text-indigo-600 transition" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">{{ __("Confirm New Password") }}</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg pr-12 focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Confirm new password') }}" required autocomplete="new-password">
                    <button type="button" tabindex="-1" class="absolute top-3 right-4 text-gray-500 hover:text-indigo-600 transition" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                {{ captcha_render() }}
            </div>

            <div class="msg-error mb-4"></div>

            <button type="submit" class="mb-6 w-full rounded-xl bg-[#181714] px-9 py-4 text-lg font-semibold text-white transition hover:-translate-y-0.5">
                {{ __("Reset Password") }}
            </button>
        </form>
        </div>
    </div>
</section>
