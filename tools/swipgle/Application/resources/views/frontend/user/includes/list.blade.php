<div class="vr__card">
    <div class="vr__settings__side">
        <div class="vr__settings__user">
            <div class="vr__settings__user__img mb-3">
                <img id="avatar_preview" src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" />
                @if (request()->routeIs('user.settings'))
                    <div class="vr__settings__user__img__change">
                        <input id="change_avatar" type="file" name="avatar" form="deatilsForm"
                            accept="image/jpg, image/jpeg, image/png" hidden />
                        <label for="change_avatar">
                            <i class="fa fa-camera"></i>
                        </label>
                    </div>
                @endif
            </div>
            <div class="vr__settings__user__title">
                <p class="mb-0 h5">{{ $user->name }}</p>
            </div>
        </div>
        <div class="vr__settings__links">
            <a href="{{ route('user.settings') }}" class="vr__settings__link @if (request()->routeIs('user.settings')) active @endif">
                <i class="fa fa-edit"></i> {{ lang('Account details', 'user') }}
            </a>
            <a href="{{ route('user.settings.password') }}" class="vr__settings__link @if (request()->routeIs('user.settings.password')) active @endif">
                <i class="fas fa-sync-alt"></i> {{ lang('Change Password', 'user') }}
            </a>
            <a href="{{ route('user.settings.2fa') }}" class="vr__settings__link @if (request()->routeIs('user.settings.2fa')) active @endif">
                <i class="fas fa-fingerprint"></i> {{ lang('2FA Authentication', 'user') }}
            </a>
        </div>
    </div>
</div>
