<div>
    @php
        fake()->seed(1)
    @endphp

    <div style="filter: blur(.25rem)" class="user-select-none">{{ fake()->text(($loop->index == 0 ? 40 : ($loop->index % 2 == 0 ? 60 : ($loop->index % 3 == 0 ? 120 : ($loop->index % 5 == 0 ? 90 : 75))))) }}</div>

    <div class="alert alert-danger mb-0 mt-3 small">
        {{ __('This is a limited report, register to generate complete reports.') }} <a href="{{ route('register') }}" class="alert-link">{{ __('Register') }}</a>
    </div>
</div>
