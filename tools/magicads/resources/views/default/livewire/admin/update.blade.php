<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Update')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Update Software') }}</h1>
                <flux:subheading size="md">{{ __('Current version') }}: {{ $current_version }}</flux:subheading>
            </div>

            @if (! $is_update_available)
                {{-- Up to date --}}
                <div class="mb-12 bg-[rgba(0,188,126,0.1)] rounded-2xl md:p-9 p-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="grow text-center md:text-left">
                            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ config('app.name') }} {{ __('is fully up to date') }}</h2>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ __('You are running version') }} {{ $current_version }}</p>
                        </div>
                        <div class="ml-auto">
                            <flux:icon.circle-check-big class="w-12 h-12 text-[#00bc7e]" />
                        </div>
                    </div>
                </div>
            @else
                {{-- Update available --}}
                @php
                    $newVersion = $latest_version['version']
                        ?? $latest_version['latest_version']
                        ?? '';
                @endphp
                <div class="mb-12 bg-indigo-50 rounded-2xl md:p-9 p-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="grow text-center md:text-left">
                            <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">
                                {{ __('New version') }} {{ $newVersion }} {{ __('update available') }}
                            </h2>
                            <flux:button
                                wire:click="save"
                                variant="primary"
                                class="md:w-1/2 w-full py-6 rounded-xl cursor-pointer shadow-lg hover:shadow-xl transition-all"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                :disabled="$is_upgrading"
                            >
                                <span wire:loading.remove wire:target="save">{{ __('Install Update') }}</span>
                                <span wire:loading wire:target="save">{{ __('Installing, please wait...') }}</span>
                            </flux:button>
                            <p wire:loading wire:target="save" class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                {{ __('Downloading and extracting update files. Please do not refresh the page.') }}
                            </p>
                        </div>
                        <div class="ml-auto">
                            <flux:icon.cloud-sync class="w-12 h-12 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                </div>
            @endif

            <div class="mb-5">
                <h1 class="font-bold text-xl text-gray-700 dark:text-neutral-200">{{ __('Changelogs') }}</h1>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.8 - Released July 14, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Ad Performance Analytics plugin added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">SaaS Business plugin updated</span></li>  
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Theme installation issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Account Analysis minor issue fixed</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.7 - Released July 9, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Coupons Codes plugin added (Free for Extended License)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Gift Cards plugin added (Free for Extended License)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Seedance video model supports now direct provider API, fal.ai API and Kie.ai API</span></li> 
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Kling video model supports now direct provider API, fal.ai API and Kie.ai API</span></li> 
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Seendance and Kling model different video quality options added</span></li> 
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">SaaS Business plugin updated</span></li>  
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Main language file en.json updated to include all missing translations</span></li>  
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Login/Registration pages updated to show fiels in the selected locale</span></li> 
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Hidden Plans can now be manually assigned as well</span></li> 
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Referral system minor issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Wallet Gateway billing checkout issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">User avatar image position fixed on the minimized side panel</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Global currency value sync on dashboard fixed</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.6 - Released July 1, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Channel Broadcast (Whatsapp, Telegram, Messenger, Slack, Email) plugin added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Cloudflare R2 Storage plugin added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Google Cloud Storage plugin added (Paid)</span></li> 
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">SaaS Business plugin updated</span></li>  
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.5 - Released June 27, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Social Media plugin added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Prompt Marketplace plugin added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Brank Kit feature added to Product Photoshoot plugin</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Brank Kit feature added to Fashion Studio plugin</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Manual Subscription Plan assiging to users option added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">SaaS Business plugin updated</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Language Manager issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Favicon issue fixed</span></li>                            
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Manual user creation role issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Minor issues fixed</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.4 - Released June 24, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Teams plugin added (Free)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Corporate Frontend Theme added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Enigma Frontend Theme added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Projects feature added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Social Media login on mobile devices improved</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.3 - Released June 20, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">UGC Factory extension added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Avatar Studio extension added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Wasabi storage extension added (Free)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Amazon S3 storage extension added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">SaaS Business extension v1.2 update</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Feature access control via Subscription Plans added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Full free tier control option added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Error message handling improved</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Subscription cancellation option added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">OpenAI image quality selection added for Image Studio by admin group</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Affiliate page added to user panel as well for tracking</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Brand kit application on Image Studio improved</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Reusing prompt is cleaned from meta information</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Image Studio final ad image quality improved</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Frontend plan selection redirection issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Image Studio steps redirection issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Brand logo appearing on the final result issue fixed</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.2 - Released June 18, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Fashion Studio extension added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Product Photoshoot extension added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Locale select option moved to the top on frontend.</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Video playback improved at frontend.</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.1 - Released June 13, 2026</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Frontend Classic theme added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Frontend Dark theme added (Paid)</span></li>
                            <li class="mb-4"><span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span><span class="text-gray-500 text-[13px]">Prompt Library added</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Results view box improved</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Ad Copy listing page improved</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Brands logo view improved</span></li>
                            <li class="mb-4"><span class="bg-indigo-50 text-indigo-700 text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Update</span><span class="text-gray-500 text-[13px]">Plugin installation mechanism improved</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Bank Transfer gateway minor issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Finance Dashboard view issue fixed</span></li>
                            <li class="mb-4"><span class="bg-[#F8D7DA] text-[#B02A37] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">Fix</span><span class="text-gray-500 text-[13px]">Ad Copy extra variants view minor issue fixed</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border border-(--default-border-color) rounded-2xl p-8 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div>
                    <div class="mt-5">
                        <span class="bg-gray-200 text-[#1e1e2d] text-sm px-6 py-2 rounded-full font-semibold">{{ __('Version') }} 1.0 - Initial Release</span>
                    </div>

                    <div class="mt-6">
                        <ul>
                            <li class="mb-4">
                                <span class="bg-[rgba(0,188,126,0.1)] text-[#00bc7e] text-[10px] uppercase px-5 py-1.5 rounded-[20px] font-semibold mr-2">New</span>
                                <span class="text-gray-500 text-[13px]">MagicAds Initial Release</span>
                            </li>                           
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
