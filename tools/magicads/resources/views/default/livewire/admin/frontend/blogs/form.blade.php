<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-8/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.frontend.blogs')" separator="slash" class="text-xs">{{ __('Blogs Manager') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $post ? __('Edit') : __('Create') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <div class="md:flex md:items-center md:justify-between md:mb-6">
                    <h1 class="font-bold md:text-2xl mb-2">{{ $post ? __('Edit Blog Post') : __('Create Blog Post') }}</h1>
                    <flux:button icon="chevron-left" :href="route('admin.frontend.blogs')" wire:navigate variant="primary" class="hover:bg-blue-500 rounded-xl cursor-pointer">{{ __('Return') }}</flux:button>
                </div>
            </div>

            <flux:fieldset>
                <div class="flex gap-4 flex-col">

                    {{-- Content --}}
                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('Content') }}</h2>

                        <flux:field class="mb-6">
                            <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Title') }}</flux:label>
                            <flux:input wire:model="title" placeholder="{{ __('Enter the post title') }}" required />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Slug') }}</flux:label>
                            <flux:input wire:model="slug" placeholder="{{ __('auto-generated-from-title') }}" />
                            <flux:description>{{ __('Leave blank to auto-generate from the title. Lowercase letters, numbers and dashes only.') }}</flux:description>
                            <flux:error name="slug" />
                        </flux:field>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Excerpt') }}</flux:label>
                            <flux:textarea wire:model="excerpt" rows="2" placeholder="{{ __('Short summary shown in listings and meta description fallback') }}" />
                            <flux:error name="excerpt" />
                        </flux:field>

                        <flux:field class="mb-2">
                            <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Body') }}</flux:label>
                            <flux:textarea wire:model="content" rows="14" placeholder="{{ __('Write your post content here. HTML is supported.') }}" />
                            <flux:description>{{ __('HTML is rendered as-is on the post page. Reading time is calculated automatically.') }}</flux:description>
                            <flux:error name="content" />
                        </flux:field>
                    </div>

                    {{-- Featured image --}}
                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('Featured Image') }}</h2>

                        <div class="flex flex-col gap-4 md:flex-row md:items-start">
                            <div class="flex-1">
                                <flux:field>
                                    <flux:label>{{ __('Cover Image') }}</flux:label>
                                    <flux:input type="file" wire:model="featured_image" accept="image/*" />
                                    <flux:description>{{ __('JPG, PNG or WEBP. Max 5MB. Recommended 1200×630.') }}</flux:description>
                                    <flux:error name="featured_image" />
                                </flux:field>

                                <flux:field class="mt-5">
                                    <flux:label>{{ __('Image Alt Text') }}</flux:label>
                                    <flux:input wire:model="featured_image_alt" placeholder="{{ __('Describe the image for accessibility & SEO') }}" />
                                    <flux:error name="featured_image_alt" />
                                </flux:field>
                            </div>

                            <div class="flex-shrink-0">
                                @if ($featured_image)
                                    <div class="relative">
                                        <img src="{{ $featured_image->temporaryUrl() }}" alt="preview" class="h-28 w-44 rounded-lg border border-(--default-border-color) object-cover dark:border-white/8" />
                                    </div>
                                @elseif ($featured_image_path)
                                    <div class="relative">
                                        <img src="{{ \Illuminate\Support\Str::startsWith($featured_image_path, ['http', 'data:']) ? $featured_image_path : URL::asset($featured_image_path) }}"
                                             alt="current cover" class="h-28 w-44 rounded-lg border border-(--default-border-color) object-cover dark:border-white/8" />
                                        <button type="button" wire:click="removeFeaturedImage"
                                                class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-300">
                                            <flux:icon.trash class="size-2.5" /> {{ __('Remove') }}
                                        </button>
                                    </div>
                                @else
                                    <div class="flex h-28 w-44 items-center justify-center rounded-lg border border-dashed border-(--default-border-color) text-zinc-400 dark:border-white/8">
                                        <flux:icon.photo class="size-7" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Organization --}}
                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('Organization') }}</h2>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <flux:field class="mb-2">
                                <flux:label>{{ __('Category') }}</flux:label>
                                <flux:input wire:model="category" placeholder="{{ __('e.g. Product, Marketing') }}" />
                                <flux:error name="category" />
                            </flux:field>

                            <flux:field class="mb-2">
                                <flux:label>{{ __('Tags') }}</flux:label>
                                <flux:input wire:model="tags" placeholder="{{ __('ads, automation, ai') }}" />
                                <flux:description>{{ __('Comma separated.') }}</flux:description>
                                <flux:error name="tags" />
                            </flux:field>

                            <flux:field class="mb-2">
                                <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Author Name') }}</flux:label>
                                <flux:input wire:model="author_name" placeholder="{{ __('Author display name') }}" required />
                                <flux:error name="author_name" />
                            </flux:field>

                            <flux:field class="mb-2">
                                <flux:label>{{ __('Author Role') }}</flux:label>
                                <flux:input wire:model="author_role" placeholder="{{ __('e.g. Head of Growth') }}" />
                                <flux:error name="author_role" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- Publication --}}
                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('Publication') }}</h2>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <flux:field class="mb-2">
                                <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Status') }}</flux:label>
                                <flux:select wire:model="status">
                                    <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
                                    <flux:select.option value="published">{{ __('Published') }}</flux:select.option>
                                    <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                                </flux:select>
                                <flux:error name="status" />
                            </flux:field>

                            <flux:field class="mb-2">
                                <flux:label>{{ __('Publish Date') }}</flux:label>
                                <flux:input type="datetime-local" wire:model="published_at" />
                                <flux:description>{{ __('Leave blank when publishing to use the current time.') }}</flux:description>
                                <flux:error name="published_at" />
                            </flux:field>
                        </div>

                        <div class="md:border border-(--default-border-color) md:p-5 rounded-xl mt-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field variant="inline">
                                <flux:label>{{ __('Mark as Featured') }}</flux:label>
                                <flux:description><small>{{ __('Featured posts are pinned to the top of the blog listing.') }}</small></flux:description>
                                <flux:switch wire:model.live="is_featured" />
                                <flux:error name="is_featured" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- SEO --}}
                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('SEO') }}</h2>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Meta Title') }}</flux:label>
                            <flux:input wire:model="meta_title" placeholder="{{ __('Defaults to the post title') }}" maxlength="70" />
                            <flux:description>{{ __('Up to 70 characters.') }}</flux:description>
                            <flux:error name="meta_title" />
                        </flux:field>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Meta Description') }}</flux:label>
                            <flux:textarea wire:model="meta_description" rows="2" placeholder="{{ __('Defaults to the excerpt') }}" maxlength="160" />
                            <flux:description>{{ __('Up to 160 characters.') }}</flux:description>
                            <flux:error name="meta_description" />
                        </flux:field>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Meta Keywords') }}</flux:label>
                            <flux:input wire:model="meta_keywords" placeholder="{{ __('comma, separated, keywords') }}" />
                            <flux:error name="meta_keywords" />
                        </flux:field>

                        <flux:field class="mb-2">
                            <flux:label>{{ __('Canonical URL') }}</flux:label>
                            <flux:input type="url" wire:model="canonical_url" placeholder="{{ __('https://...') }}" />
                            <flux:description>{{ __('Only set this if the canonical version lives on another URL.') }}</flux:description>
                            <flux:error name="canonical_url" />
                        </flux:field>
                    </div>
                </div>

                <div class="flex w-full justify-center mt-4">
                    <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">
                        {{ $post ? __('Update Post') : __('Create Post') }}
                    </flux:button>
                </div>
            </flux:fieldset>
        </div>
    </div>
</div>
