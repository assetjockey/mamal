<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Activation')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('License Activation') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Manage your license activation and deactivation') }}</flux:subheading>
            </div>

            <!-- Activation Status Card -->
            <div class="mb-12 border border-neutral-200 rounded-2xl md:p-8 p-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color) ">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <!-- SVG Key Icon -->
                    <div class="flex-shrink-0">
                        @if ($isActivated)
                            <svg class="w-32 h-32" viewBox="0 0 111.81 122.88" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#51B53C" d="M55.71,0c20.85,13.21,39.68,19.47,55.85,17.99c2.82,57.1-18.26,90.82-55.63,104.89C19.84,109.71-1.5,77.44,0.08,17.12C19.06,18.12,37.67,14.01,55.71,0z"/>
                                <path fill="#FFFFFF" d="M55.73,7.05c18.45,11.7,35.13,17.23,49.43,15.92c2.5,50.54-16.17,80.39-49.24,92.85C23.98,104.16,5.09,75.6,6.49,22.21C23.29,23.09,39.77,19.46,55.73,7.05z"/>
                                <path fill="#51B53C" d="M56.24,19.54c14.22,9.01,27.06,13.27,38.08,12.27c1.92,38.94-12.45,61.93-37.94,71.53c-0.16-0.06-0.32-0.12-0.48-0.18c-0.16,0.06-0.32,0.12-0.48,0.18c-25.48-9.6-39.86-32.59-37.94-71.53c11.02,1.01,23.87-3.26,38.08-12.27l0.33,0.25z"/>
                                <path fill="#FFFFFF" d="M35.44,58.28l7.47-0.1l0.56,0.14c1.51,0.87,2.93,1.86,4.26,2.99c0.96,0.81,1.87,1.69,2.74,2.65c2.68-4.31,5.54-8.28,8.56-11.92c3.31-3.99,5.38-6.18,9.06-9.49l0.73-0.28h8.16l-1.65,1.82c-5.05,5.61-8.21,9.99-12.35,15.97c-4.15,6-7.85,12.18-11.15,18.54l-1.03,1.98l-0.94-2.02c-1.74-3.73-3.82-7.15-6.3-10.21c-2.48-3.06-5.37-5.78-8.74-8.09z"/>
                            </svg>
                        @else
                            <svg class="w-32 h-32" viewBox="0 0 111.81 122.88" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#E11D48" d="M55.71,0c20.85,13.21,39.68,19.47,55.85,17.99c2.82,57.1-18.26,90.82-55.63,104.89C19.84,109.71-1.5,77.44,0.08,17.12C19.06,18.12,37.67,14.01,55.71,0z"/>
                                <path fill="#FFFFFF" d="M55.73,7.05c18.45,11.7,35.13,17.23,49.43,15.92c2.5,50.54-16.17,80.39-49.24,92.85C23.98,104.16,5.09,75.6,6.49,22.21C23.29,23.09,39.77,19.46,55.73,7.05z"/>
                                <path fill="#E11D48" d="M56.24,19.54c14.22,9.01,27.06,13.27,38.08,12.27c1.92,38.94-12.45,61.93-37.94,71.53c-0.16-0.06-0.32-0.12-0.48-0.18c-0.16,0.06-0.32,0.12-0.48,0.18c-25.48-9.6-39.86-32.59-37.94-71.53c11.02,1.01,23.87-3.26,38.08-12.27l0.33,0.25z"/>
                                <path fill="#FFFFFF" d="M55.9,42.86c2.05,0,3.71,1.66,3.71,3.71v23.66c0,2.05-1.66,3.71-3.71,3.71s-3.71-1.66-3.71-3.71V46.57C52.19,44.52,53.85,42.86,55.9,42.86L55.9,42.86z M55.9,80.2c2.31,0,4.18,1.87,4.18,4.18s-1.87,4.18-4.18,4.18s-4.18-1.87-4.18-4.18S53.59,80.2,55.9,80.2L55.9,80.2z"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Status Info -->
                    <div class="flex-grow text-center md:text-left">
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">{{ __('License Status') }}</h2>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-center md:justify-start gap-3">
                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ __('Status:') }}</span>
                                @if ($isActivated)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('Activated') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ __('Not Activated') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-center md:justify-start gap-3">
                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ __('License Type:') }}</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ $this->licenseTypeLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activation Form -->
            <div class="border border-neutral-200 rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-white">{{ __('Activation Details') }}</h3>
                </div>

                <div class="space-y-5">
                    <flux:input type="password" wire:model="license" label="{{ __('Envato Activation Code') }}" placeholder="XXXX-XXXX-XXXX-XXXX" :readonly="$isActivated"/>
                    <flux:input type="password" wire:model="username" label="{{ __('Envato Username') }}" placeholder="{{ __('Your Envato username') }}" :readonly="$isActivated"/>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                @if ($isActivated)
                    <flux:button wire:click="deactivate" variant="danger" class="md:w-1/2 w-full py-6 rounded-xl cursor-pointer shadow-lg hover:shadow-xl transition-all"
                        wire:loading.attr="disabled" wire:target="deactivate">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0L21 21"/>
                        </svg>
                        {{__('Deactivate License')}}
                    </flux:button>
                @else
                    <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full py-6 rounded-xl cursor-pointer shadow-lg hover:shadow-xl transition-all"
                        wire:loading.attr="disabled" wire:target="save">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{__('Activate License')}}
                    </flux:button>
                @endif
            </div>

        </div>
    </div>
</div>
