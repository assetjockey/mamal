<section class="relative w-screen min-h-screen flex items-stretch overflow-hidden bg-white overflow-x-hidden">
    @include("partials/login-screen", ["name" => __("Create an account & get started.")])

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
        <form class="relative w-full space-y-5 rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-8 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] md:p-12" action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="mb-10 text-center">
                <span class="mb-5 inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">
                    {{ __("Create workspace") }}
                </span>
                <h1 class="mb-4 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __("Create your account") }}</h1>
                <p class="text-base leading-8 text-[#6d685f]">{{ __("Claim your username, then continue into your Bio page and QR workspace.") }}</p>
            </div>

            <div>
                <label for="name" class="block text-gray-700 font-semibold mb-2">{{ __("Full Name") }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Enter your full name') }}" required>
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">{{ __("Email Address") }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Enter your email address') }}" required>
            </div>

            <div>
                <label for="username" class="block text-gray-700 font-semibold mb-2">{{ __("Username") }}</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Choose a username') }}" required>
            </div>

            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-2">{{ __("Password") }}</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Enter your password') }}" required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">{{ __("Confirm Password") }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" placeholder="{{ __('Re-enter your password') }}" required>
            </div>

            <div>
                <label for="timezone" class="block text-gray-700 font-semibold mb-2">{{ __("Timezone") }}</label>
                <select id="timezone" name="timezone" class="w-full px-4 py-3.5 text-gray-700 font-medium bg-white border border-gray-300 rounded-lg focus:ring focus:ring-indigo-300 outline-none" required>
                    <option value="">{{ __("Select your timezone") }}</option>
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ old('timezone') == $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                {{ captcha_render() }}
            </div>

            <div class="flex items-center">
                <input class="w-4 h-4" id="accept_terms" name="accept_terms" type="checkbox" value="1" required>
                <label class="ml-2 text-gray-700 font-medium" for="accept_terms">
                    <span>{{ __("I agree to the") }}</span>
                    <a class="text-indigo-600 hover:text-indigo-700" href="{{ url('terms-of-service') }}">{{ __("Terms & Conditions") }}</a>
                </label>
            </div>

            <div class="msg-error mb-2"></div>

            <button type="submit" class="mb-6 w-full rounded-xl bg-[#181714] px-9 py-4 text-lg font-semibold text-white transition hover:-translate-y-0.5">
                {{ __("Sign Up") }}
            </button>

            <p class="text-center pt-4 text-gray-600">
                {{ __("Already have an account?") }}
                <a href="{{ url('auth/login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">{{ __("Sign in") }}</a>
            </p>
        </form>
        </div>
    </div>
</section>
