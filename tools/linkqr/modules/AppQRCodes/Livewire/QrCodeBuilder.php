<?php

namespace Modules\AppQRCodes\Livewire;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AppFiles\Models\AppFile;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Support\BrandMosaicQrRenderer;
use Modules\AppQRCodes\Support\GradientRoundedQrRenderer;
use Modules\AppQRCodes\Support\QrStyleTemplateCatalog;
use Modules\AppTeams\Support\ActivityLogger;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Edit QR Code')]
class QrCodeBuilder extends Component
{
    public int $qrCodeId;

    public array $form = [
        'name' => '',
        'destination_url' => '',
        'status' => 'active',
        'foreground_color' => '#0f172a',
        'background_color' => '#ffffff',
        'pattern' => 'square',
        'design_mode' => 'basic',
        'style_template' => 'clean_card',
        'public_template' => 'split_showcase',
        'logo_url' => '',
        'qr_shape' => 'circle',
        'ecc' => 'high',
        'output_px' => 1024,
        'logo_size' => 18,
        'dark_min' => 47,
        'dark_max' => 105,
        'ghost_strength' => 80,
        'ghost_threshold' => 8,
        'fallback_dark' => '#0f172a',
        'use_image_colors' => true,
        'brand_kit_id' => '',
        'custom_domain_id' => '',
        'utm_preset_id' => '',
        'tracking_pixel_ids' => [],
        'dynamic_rules' => [],
    ];

    public function mount(AppQrCode $qrCode): void
    {
        abort_unless($this->canUseQrCodes(), 403);
        abort_unless((int) $qrCode->owner_user_id === TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()), 404);

        $this->qrCodeId = (int) $qrCode->id;
        $this->fillForm($qrCode);
    }

    public function saveQrCode(): void
    {
        $qrCode = $this->ownedQrCode();
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        $this->clearUnavailableBrandFields();

        $validator = validator($this->form, [
            'name' => ['required', 'string', 'max:160'],
            'destination_url' => [
                'nullable',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isAllowedQrDestination((string) $value)) {
                        $fail(__('Enter a valid URL or QR payload such as sms:, mailto:, tel:, geo:, or WIFI:.'));
                    }
                },
            ],
            'status' => ['required', 'in:active,draft'],
            'foreground_color' => ['nullable', 'string', 'max:24'],
            'background_color' => ['nullable', 'string', 'max:24'],
            'pattern' => ['required', 'in:square,rounded,dots,mosaic,sticker,outlined,gradient_rounded'],
            'design_mode' => ['required', 'in:basic,roundness,mosaic'],
            'style_template' => ['required', Rule::in(array_keys($this->qrTemplates()))],
            'public_template' => ['required', Rule::in(array_keys($this->publicTemplates()))],
            'logo_url' => ['nullable', 'string', 'max:2000'],
            'qr_shape' => ['required', 'in:circle,square,rounded,diamond'],
            'ecc' => ['required', 'in:low,medium,quartile,high'],
            'output_px' => ['required', 'integer', 'in:512,768,1024,1536,2048'],
            'logo_size' => ['required', 'integer', 'min:8', 'max:28'],
            'dark_min' => ['required', 'integer', 'min:0', 'max:100'],
            'dark_max' => ['required', 'integer', 'min:1', 'max:140'],
            'ghost_strength' => ['required', 'integer', 'min:0', 'max:100'],
            'ghost_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'fallback_dark' => ['nullable', 'string', 'max:24'],
            'use_image_colors' => ['boolean'],
            'brand_kit_id' => ['nullable', Rule::exists('app_brand_kits', 'id')->where('owner_user_id', $ownerUserId)],
            'custom_domain_id' => ['nullable', Rule::exists('custom_domains', 'id')->where('owner_user_id', $ownerUserId)],
            'utm_preset_id' => ['nullable', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $ownerUserId)],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $ownerUserId)],
            'dynamic_rules' => ['array', 'max:'.$this->dynamicRuleLimitForValidation()],
            'dynamic_rules.*.name' => ['nullable', 'string', 'max:120'],
            'dynamic_rules.*.priority' => ['nullable', 'integer', 'min:1', 'max:999'],
            'dynamic_rules.*.enabled' => ['boolean'],
            'dynamic_rules.*.url' => [
                'nullable',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);

                    if ($value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail(__('Enter a valid destination URL for each dynamic rule.'));
                    }
                },
            ],
            'dynamic_rules.*.countries' => ['nullable', 'string', 'max:300'],
            'dynamic_rules.*.devices' => ['array'],
            'dynamic_rules.*.devices.*' => ['string', 'in:MOBILE,DESKTOP,TABLET'],
            'dynamic_rules.*.hours_from' => ['nullable', 'integer', 'min:0', 'max:23'],
            'dynamic_rules.*.hours_to' => ['nullable', 'integer', 'min:0', 'max:23'],
            'dynamic_rules.*.start_at' => ['nullable', 'date'],
            'dynamic_rules.*.end_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
            $this->dispatch('app-toast', type: 'error', message: $validator->errors()->first());

            return;
        }

        $validated = $validator->validated();

        try {
            $trackingPixelIds = collect($validated['tracking_pixel_ids'] ?? [])
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $dynamicRules = $this->normalizeDynamicRules((array) ($validated['dynamic_rules'] ?? []));

            $qrCode->update([
                'name' => $validated['name'],
                'destination_url' => $validated['destination_url'] ?: url('/'),
                'status' => $validated['status'],
                'foreground_color' => $validated['foreground_color'] ?: '#0f172a',
                'background_color' => $validated['background_color'] ?: '#ffffff',
                'pattern' => $validated['pattern'],
                'logo_url' => trim((string) ($validated['logo_url'] ?? '')) ?: null,
                'settings' => array_merge((array) $qrCode->settings, [
                    'qr_shape' => $validated['qr_shape'],
                    'design_mode' => $validated['design_mode'],
                    'style_template' => $validated['style_template'],
                    'public_template' => $validated['public_template'],
                    'ecc' => $validated['ecc'],
                    'output_px' => (int) $validated['output_px'],
                    'logo_size' => (int) $validated['logo_size'],
                    'dark_min' => (int) $validated['dark_min'],
                    'dark_max' => (int) $validated['dark_max'],
                    'ghost_strength' => (int) $validated['ghost_strength'],
                    'ghost_threshold' => (int) $validated['ghost_threshold'],
                    'fallback_dark' => $validated['fallback_dark'] ?: '#0f172a',
                    'use_image_colors' => (bool) $validated['use_image_colors'],
                    'brand_kit_id' => filled($validated['brand_kit_id'] ?? null) ? (int) $validated['brand_kit_id'] : null,
                    'custom_domain_id' => filled($validated['custom_domain_id'] ?? null) ? (int) $validated['custom_domain_id'] : null,
                    'utm_preset_id' => filled($validated['utm_preset_id'] ?? null) ? (int) $validated['utm_preset_id'] : null,
                    'tracking_pixel_ids' => $trackingPixelIds,
                    'dynamic_rules' => $dynamicRules,
                ]),
            ]);

            $this->fillForm($qrCode->fresh());
            app(ActivityLogger::class)->log('qr.updated', [
                'team_id' => $qrCode->team_id,
                'owner_user_id' => $qrCode->owner_user_id,
                'subject_type' => AppQrCode::class,
                'subject_id' => $qrCode->id,
                'metadata' => ['name' => $qrCode->name],
            ]);
            $this->dispatch('app-toast', type: 'success', message: __('QR code saved.'));
        } catch (\Throwable $exception) {
            report($exception);

            $message = __('Could not save this QR code. Please check the data and try again.');
            $this->setErrorBag(['save' => $message]);
            $this->dispatch('app-toast', type: 'error', message: $message);
        }
    }

    public function updatedFormBrandKitId(mixed $value): void
    {
        $brandKit = app(BrandOperations::class)->brandKit((int) $value, TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()));

        if (! $brandKit) {
            return;
        }

        $this->form['foreground_color'] = $brandKit->primary_color ?: $this->form['foreground_color'];
        $this->form['fallback_dark'] = $brandKit->secondary_color ?: $this->form['fallback_dark'];
        $this->form['logo_url'] = $brandKit->logo_url ?: $this->form['logo_url'];
    }

    public function updatedFormStyleTemplate(mixed $value): void
    {
        $this->applyQrTemplate((string) $value);
    }

    public function updatedFormDesignMode(mixed $value): void
    {
        $mode = (string) $value;
        $this->form['design_mode'] = in_array($mode, ['basic', 'roundness', 'mosaic'], true) ? $mode : 'basic';
        $template = match ($this->form['design_mode']) {
            'roundness' => 'rounded_gradient',
            'mosaic' => 'default_mosaic',
            default => 'clean_card',
        };

        $this->applyQrTemplate($template);
        $this->form['style_template'] = $template;
    }

    public function updatedForm(mixed $value, string $key): void
    {
        if ($key === 'style_template') {
            $this->applyQrTemplate((string) $value);
        }
    }

    public function addDynamicRule(): void
    {
        if ($this->hasReachedDynamicRuleLimit()) {
            $this->dispatch('app-toast', type: 'warning', message: __('Dynamic rule limit reached. Upgrade your plan to add more redirect rules.'));

            return;
        }

        $this->form['dynamic_rules'][] = $this->blankDynamicRule(count((array) $this->form['dynamic_rules']) + 1);
    }

    public function duplicateDynamicRule(int $index): void
    {
        $rule = (array) data_get($this->form, "dynamic_rules.{$index}", []);

        if ($rule === []) {
            return;
        }

        if ($this->hasReachedDynamicRuleLimit()) {
            $this->dispatch('app-toast', type: 'warning', message: __('Dynamic rule limit reached. Upgrade your plan to add more redirect rules.'));

            return;
        }

        $rule['name'] = trim((string) ($rule['name'] ?? __('Rule'))).' '.__('copy');
        $rule['priority'] = (int) ($rule['priority'] ?? 100) + 1;
        $this->form['dynamic_rules'][] = array_merge($this->blankDynamicRule(), $rule);
    }

    public function removeDynamicRule(int $index): void
    {
        $rules = array_values((array) $this->form['dynamic_rules']);
        unset($rules[$index]);
        $this->form['dynamic_rules'] = array_values($rules);
    }

    protected function applyQrTemplate(string $value): void
    {
        $template = $this->qrTemplates()[(string) $value] ?? null;

        if (! $template) {
            return;
        }

        $this->form['pattern'] = $template['pattern'];
        $this->form['design_mode'] = match ($template['pattern']) {
            'mosaic' => 'mosaic',
            'gradient_rounded' => 'roundness',
            default => 'basic',
        };
        $this->form['foreground_color'] = $template['foreground'];
        $this->form['background_color'] = $template['background'];
        $this->form['fallback_dark'] = $template['fallback'];
        $this->form['qr_shape'] = $template['shape'];
    }

    public function render(): View
    {
        $qrCode = $this->ownedQrCode();
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $brandOps = app(BrandOperations::class);
        $selectedDomain = $brandOps->verifiedDomain((int) data_get($this->form, 'custom_domain_id'), $ownerUserId)
            ?: (filled(data_get($this->form, 'custom_domain_id')) ? null : $brandOps->defaultQrDomain($ownerUserId));
        $types = collect(AppQrCode::typeCatalog());
        $typeMeta = $types->firstWhere('key', $qrCode->type) ?: [
            'label' => str_replace('_', ' ', $qrCode->type),
            'kind' => $qrCode->short_code ? __('dynamic') : __('static'),
            'icon' => 'fa-qrcode',
            'description' => '',
        ];

        return view('appqrcodes::builder', [
            'qrCode' => $qrCode,
            'typeMeta' => $typeMeta,
            'brandKits' => $this->canUseBrandKit() ? $brandOps->brandKits($ownerUserId) : collect(),
            'customDomains' => $this->canUseCustomDomains() ? $brandOps->domains($ownerUserId) : collect(),
            'utmPresets' => $this->canUseUtmPresets() ? $brandOps->utmPresets($ownerUserId) : collect(),
            'trackingPixels' => $this->canUseTrackingPixels() ? $brandOps->pixels($ownerUserId) : collect(),
            'qrTemplates' => $this->qrTemplates(),
            'publicTemplates' => $this->publicTemplates(),
            'displayShortUrl' => $brandOps->customUrl($selectedDomain, 'q/'.($qrCode->short_code ?: ''), $qrCode->shortUrl() ?: ''),
            'resolvedAiArtworkUrl' => $this->resolvedAiArtworkUrl($qrCode),
            'qrSvg' => $this->previewSvg(
                $brandOps->customUrl($selectedDomain, 'q/'.($qrCode->short_code ?: ''), $qrCode->shortUrl() ?: (string) data_get($this->form, 'destination_url', url('/'))),
                (string) data_get($this->form, 'foreground_color', '#0f172a'),
                (string) data_get($this->form, 'background_color', '#ffffff'),
                (string) data_get($this->form, 'ecc', 'high'),
            ),
            'canUseQrTypeBuilders' => $this->canUseQrTypeBuilders(),
            'canUseBrandKit' => $this->canUseBrandKit(),
            'canUseCustomDomains' => $this->canUseCustomDomains(),
            'canUseUtmPresets' => $this->canUseUtmPresets(),
            'canUseTrackingPixels' => $this->canUseTrackingPixels(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Edit QR Code'),
        ]);
    }

    protected function resolvedAiArtworkUrl(AppQrCode $qrCode): string
    {
        $fileId = (int) data_get($qrCode->settings, 'ai_qr.background_file_id');

        if ($fileId > 0) {
            $file = AppFile::query()->find($fileId);

            if ($file) {
                $path = (string) $file->path;
                $disk = (string) $file->disk;

                if ($path !== '' && $disk !== '' && Storage::disk($disk)->exists($path)) {
                    $mimeType = $file->mime_type ?: 'image/png';

                    return 'data:'.$mimeType.';base64,'.base64_encode(Storage::disk($disk)->get($path));
                }

                return URL::signedRoute('portal.files.publish-preview', ['file' => $file]);
            }
        }

        return trim((string) data_get($qrCode->settings, 'ai_qr.background_url', ''));
    }

    protected function fillForm(AppQrCode $qrCode): void
    {
        $styleTemplate = (string) data_get($qrCode->settings, 'style_template', 'clean_card');

        if (! array_key_exists($styleTemplate, $this->qrTemplates())) {
            $styleTemplate = match ((string) $qrCode->pattern) {
                'mosaic' => 'default_mosaic',
                'gradient_rounded' => 'rounded_gradient',
                default => 'clean_card',
            };
        }

        $this->form = [
            'name' => (string) $qrCode->name,
            'destination_url' => (string) ($qrCode->destination_url ?: ''),
            'status' => (string) $qrCode->status,
            'foreground_color' => (string) $qrCode->foreground_color,
            'background_color' => (string) $qrCode->background_color,
            'pattern' => (string) $qrCode->pattern,
            'design_mode' => (string) data_get($qrCode->settings, 'design_mode', match ((string) $qrCode->pattern) {
                'mosaic' => 'mosaic',
                'gradient_rounded' => 'roundness',
                default => 'basic',
            }),
            'style_template' => $styleTemplate,
            'public_template' => (string) data_get($qrCode->settings, 'public_template', 'split_showcase'),
            'logo_url' => (string) ($qrCode->logo_url ?: ''),
            'qr_shape' => (string) data_get($qrCode->settings, 'qr_shape', 'circle'),
            'ecc' => (string) data_get($qrCode->settings, 'ecc', 'high'),
            'output_px' => (int) data_get($qrCode->settings, 'output_px', 1024),
            'logo_size' => (int) data_get($qrCode->settings, 'logo_size', 18),
            'dark_min' => (int) data_get($qrCode->settings, 'dark_min', 47),
            'dark_max' => (int) data_get($qrCode->settings, 'dark_max', 105),
            'ghost_strength' => (int) data_get($qrCode->settings, 'ghost_strength', 80),
            'ghost_threshold' => (int) data_get($qrCode->settings, 'ghost_threshold', 8),
            'fallback_dark' => (string) data_get($qrCode->settings, 'fallback_dark', '#0f172a'),
            'use_image_colors' => (bool) data_get($qrCode->settings, 'use_image_colors', true),
            'brand_kit_id' => (string) data_get($qrCode->settings, 'brand_kit_id', ''),
            'custom_domain_id' => (string) data_get($qrCode->settings, 'custom_domain_id', ''),
            'utm_preset_id' => (string) data_get($qrCode->settings, 'utm_preset_id', ''),
            'tracking_pixel_ids' => collect((array) data_get($qrCode->settings, 'tracking_pixel_ids', []))
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'dynamic_rules' => $this->rulesForForm((array) data_get($qrCode->settings, 'dynamic_rules', [])),
        ];
    }

    protected function normalizeDynamicRules(array $rules): array
    {
        return collect($rules)
            ->filter(fn ($rule): bool => is_array($rule))
            ->map(function (array $rule): array {
                $from = filled(data_get($rule, 'hours_from')) ? (int) data_get($rule, 'hours_from') : null;
                $to = filled(data_get($rule, 'hours_to')) ? (int) data_get($rule, 'hours_to') : null;
                $hours = ($from !== null && $to !== null)
                    ? ($from <= $to ? range($from, $to) : array_merge(range($from, 23), range(0, $to)))
                    : [];

                return [
                    'name' => trim((string) data_get($rule, 'name', 'Rule')),
                    'priority' => (int) data_get($rule, 'priority', 100),
                    'enabled' => (bool) data_get($rule, 'enabled', true),
                    'url' => trim((string) data_get($rule, 'url', '')),
                    'countries' => collect(explode(',', (string) data_get($rule, 'countries', '')))->map(fn ($value): string => strtoupper(trim((string) $value)))->filter()->unique()->values()->all(),
                    'devices' => collect((array) data_get($rule, 'devices', []))->map(fn ($value): string => strtoupper(trim((string) $value)))->filter()->values()->all(),
                    'hours' => $hours,
                    'start_at' => trim((string) data_get($rule, 'start_at', '')) ?: null,
                    'end_at' => trim((string) data_get($rule, 'end_at', '')) ?: null,
                ];
            })
            ->filter(fn (array $rule): bool => filled($rule['url']))
            ->sortBy('priority')
            ->values()
            ->all();
    }

    protected function rulesForForm(array $rules): array
    {
        return collect($rules)
            ->filter(fn ($rule): bool => is_array($rule))
            ->map(function (array $rule): array {
                $hours = collect((array) data_get($rule, 'hours', []))
                    ->map(fn ($hour): int => (int) $hour)
                    ->filter(fn (int $hour): bool => $hour >= 0 && $hour <= 23)
                    ->values();

                return [
                    'name' => trim((string) data_get($rule, 'name', __('Rule'))),
                    'priority' => (int) data_get($rule, 'priority', 100),
                    'enabled' => (bool) data_get($rule, 'enabled', true),
                    'url' => trim((string) data_get($rule, 'url', '')),
                    'countries' => collect((array) data_get($rule, 'countries', []))->map(fn ($value): string => strtoupper(trim((string) $value)))->filter()->implode(', '),
                    'devices' => collect((array) data_get($rule, 'devices', []))->map(fn ($value): string => strtoupper(trim((string) $value)))->filter()->values()->all(),
                    'hours_from' => $hours->isNotEmpty() ? $hours->min() : '',
                    'hours_to' => $hours->isNotEmpty() ? $hours->max() : '',
                    'start_at' => (string) data_get($rule, 'start_at', ''),
                    'end_at' => (string) data_get($rule, 'end_at', ''),
                ];
            })
            ->values()
            ->all();
    }

    protected function blankDynamicRule(int $number = 1): array
    {
        return [
            'name' => __('Rule :number', ['number' => $number]),
            'priority' => 100,
            'enabled' => true,
            'url' => '',
            'countries' => '',
            'devices' => [],
            'hours_from' => '',
            'hours_to' => '',
            'start_at' => '',
            'end_at' => '',
        ];
    }

    protected function publicTemplates(): array
    {
        return [
            'split_showcase' => [
                'label' => __('Split showcase'),
                'description' => __('Large copy on the left with details and media on the right.'),
                'icon' => 'fa-columns-3',
            ],
            'mobile_stack' => [
                'label' => __('Mobile stack'),
                'description' => __('Compact app-like page for menus, profiles, and quick actions.'),
                'icon' => 'fa-mobile-screen',
            ],
            'catalog_grid' => [
                'label' => __('Catalog grid'),
                'description' => __('Product-first storefront with collection cards and clear CTAs.'),
                'icon' => 'fa-grid-2',
            ],
            'editorial' => [
                'label' => __('Editorial'),
                'description' => __('Clean article-style page for events, resumes, files, and reviews.'),
                'icon' => 'fa-newspaper',
            ],
            'compact_banner' => [
                'label' => __('Compact banner'),
                'description' => __('Short branded hero with action buttons above the fold.'),
                'icon' => 'fa-rectangle-wide',
            ],
            'sidebar_profile' => [
                'label' => __('Sidebar profile'),
                'description' => __('Profile-first layout with contact facts beside the content.'),
                'icon' => 'fa-sidebar',
            ],
            'menu_board' => [
                'label' => __('Menu board'),
                'description' => __('Restaurant-style board with item groups and strong prices.'),
                'icon' => 'fa-utensils',
            ],
            'lead_capture' => [
                'label' => __('Lead capture'),
                'description' => __('Conversion-focused page for forms, quotes, and bookings.'),
                'icon' => 'fa-inbox-in',
            ],
            'event_pass' => [
                'label' => __('Event pass'),
                'description' => __('Ticket-like design for events, RSVPs, and appointments.'),
                'icon' => 'fa-ticket',
            ],
            'file_card' => [
                'label' => __('File card'),
                'description' => __('Document-style view for files, resumes, and downloads.'),
                'icon' => 'fa-file-lines',
            ],
            'review_spotlight' => [
                'label' => __('Review spotlight'),
                'description' => __('Trust-first layout for reviews and social proof.'),
                'icon' => 'fa-star',
            ],
            'resume_sheet' => [
                'label' => __('Resume sheet'),
                'description' => __('Professional profile sheet with clear contact options.'),
                'icon' => 'fa-id-card',
            ],
            'booking_card' => [
                'label' => __('Booking card'),
                'description' => __('Appointment card with date, location, and booking CTA.'),
                'icon' => 'fa-calendar-check',
            ],
            'app_download' => [
                'label' => __('App download'),
                'description' => __('App-store style page for downloads and product launches.'),
                'icon' => 'fa-mobile-button',
            ],
            'payment_card' => [
                'label' => __('Payment card'),
                'description' => __('Payment-forward layout for PayPal, UPI, PIX, and crypto.'),
                'icon' => 'fa-credit-card',
            ],
            'link_hub' => [
                'label' => __('Link hub'),
                'description' => __('Simple hub for multiple links and campaign destinations.'),
                'icon' => 'fa-link',
            ],
        ];
    }

    protected function qrTemplates(): array
    {
        return QrStyleTemplateCatalog::all();
        return [
            'clean_card' => [
                'label' => __('Default QR'),
                'description' => __('Plain QR only, no frame or sticker design.'),
                'pattern' => 'square',
                'foreground' => '#0f172a',
                'background' => '#ffffff',
                'fallback' => '#0f172a',
                'shape' => 'square',
                'accent' => '#2563eb',
                'surface' => '#ffffff',
            ],
            'rounded_gradient' => [
                'label' => __('Rounded gradient'),
                'description' => __('Rounded modules with gradient color and branded finder corners.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#b02a2a',
                'background' => '#ffffff',
                'fallback' => '#264653',
                'shape' => 'rounded',
                'accent' => '#2a9d8f',
                'surface' => '#ffffff',
            ],
            'roundness_sunset' => [
                'label' => __('Sunset round'),
                'description' => __('Warm red-orange rounded modules for retail campaigns.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#be123c',
                'background' => '#fff7ed',
                'fallback' => '#f97316',
                'shape' => 'rounded',
                'accent' => '#0f766e',
                'surface' => '#fff1f2',
            ],
            'roundness_ocean' => [
                'label' => __('Ocean round'),
                'description' => __('Blue to teal rounded QR with deep finder dots.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#1d4ed8',
                'background' => '#eff6ff',
                'fallback' => '#06b6d4',
                'shape' => 'rounded',
                'accent' => '#0e7490',
                'surface' => '#ecfeff',
            ],
            'roundness_forest' => [
                'label' => __('Forest round'),
                'description' => __('Green gradient rounded QR for stores and menus.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#15803d',
                'background' => '#f0fdf4',
                'fallback' => '#84cc16',
                'shape' => 'rounded',
                'accent' => '#047857',
                'surface' => '#dcfce7',
            ],
            'roundness_berry' => [
                'label' => __('Berry round'),
                'description' => __('Pink and violet rounded QR for creator campaigns.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#be185d',
                'background' => '#fff1f8',
                'fallback' => '#7c3aed',
                'shape' => 'rounded',
                'accent' => '#db2777',
                'surface' => '#fce7f3',
            ],
            'roundness_indigo' => [
                'label' => __('Indigo round'),
                'description' => __('Clean indigo gradient with modern blue finder corners.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#4338ca',
                'background' => '#eef2ff',
                'fallback' => '#0ea5e9',
                'shape' => 'rounded',
                'accent' => '#2563eb',
                'surface' => '#e0e7ff',
            ],
            'roundness_lime' => [
                'label' => __('Lime round'),
                'description' => __('Fresh lime and emerald rounded QR for wellness brands.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#65a30d',
                'background' => '#f7fee7',
                'fallback' => '#10b981',
                'shape' => 'rounded',
                'accent' => '#16a34a',
                'surface' => '#ecfccb',
            ],
            'roundness_carbon' => [
                'label' => __('Carbon round'),
                'description' => __('Dark graphite modules with steel finder corners.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#111827',
                'background' => '#f8fafc',
                'fallback' => '#64748b',
                'shape' => 'rounded',
                'accent' => '#334155',
                'surface' => '#e2e8f0',
            ],
            'roundness_gold' => [
                'label' => __('Gold round'),
                'description' => __('Gold and amber gradient for premium printed assets.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#92400e',
                'background' => '#fffbeb',
                'fallback' => '#f59e0b',
                'shape' => 'rounded',
                'accent' => '#ca8a04',
                'surface' => '#fef3c7',
            ],
            'roundness_candy' => [
                'label' => __('Candy round'),
                'description' => __('Bright coral and cyan gradient with playful corners.'),
                'pattern' => 'gradient_rounded',
                'foreground' => '#ef4444',
                'background' => '#fff1f2',
                'fallback' => '#06b6d4',
                'shape' => 'rounded',
                'accent' => '#ec4899',
                'surface' => '#ffe4e6',
            ],
            'slate_minimal' => [
                'label' => __('Slate minimal'),
                'description' => __('Quiet card with simple border and scan text.'),
                'pattern' => 'square',
                'foreground' => '#111827',
                'background' => '#ffffff',
                'fallback' => '#020617',
                'shape' => 'square',
                'accent' => '#64748b',
                'surface' => '#f8fafc',
            ],
            'navy_label' => [
                'label' => __('Navy square'),
                'description' => __('Navy framed square for print stickers.'),
                'pattern' => 'square',
                'foreground' => '#0f4f87',
                'background' => '#ffffff',
                'fallback' => '#0b2447',
                'shape' => 'square',
                'accent' => '#f97316',
                'surface' => '#0b2447',
            ],
            'coral_sticker' => [
                'label' => __('Coral square'),
                'description' => __('Soft coral square without CTA text.'),
                'pattern' => 'square',
                'foreground' => '#c7265b',
                'background' => '#fff1f4',
                'fallback' => '#9f1239',
                'shape' => 'square',
                'accent' => '#fb7185',
                'surface' => '#fff1f4',
            ],
            'emerald_ring' => [
                'label' => __('Emerald square'),
                'description' => __('Green square frame for storefronts.'),
                'pattern' => 'square',
                'foreground' => '#0f7a32',
                'background' => '#ffffff',
                'fallback' => '#064e3b',
                'shape' => 'square',
                'accent' => '#16a34a',
                'surface' => '#ecfdf5',
            ],
            'gold_orbit' => [
                'label' => __('Gold square'),
                'description' => __('Premium gold square frame.'),
                'pattern' => 'square',
                'foreground' => '#171717',
                'background' => '#fffaf0',
                'fallback' => '#92400e',
                'shape' => 'square',
                'accent' => '#f59e0b',
                'surface' => '#fffbeb',
            ],
            'pink_scan' => [
                'label' => __('Pink square'),
                'description' => __('Retail style pink square.'),
                'pattern' => 'square',
                'foreground' => '#be185d',
                'background' => '#fff1f8',
                'fallback' => '#831843',
                'shape' => 'square',
                'accent' => '#ec4899',
                'surface' => '#fce7f3',
            ],
            'teal_ticket' => [
                'label' => __('Teal square'),
                'description' => __('Teal square layout for offers.'),
                'pattern' => 'square',
                'foreground' => '#0f766e',
                'background' => '#f0fdfa',
                'fallback' => '#134e4a',
                'shape' => 'square',
                'accent' => '#14b8a6',
                'surface' => '#ccfbf1',
            ],
            'black_premium' => [
                'label' => __('Black premium'),
                'description' => __('High contrast luxury frame.'),
                'pattern' => 'square',
                'foreground' => '#111827',
                'background' => '#ffffff',
                'fallback' => '#030712',
                'shape' => 'square',
                'accent' => '#facc15',
                'surface' => '#111827',
            ],
            'orange_file' => [
                'label' => __('Orange square'),
                'description' => __('Document and file sharing square.'),
                'pattern' => 'square',
                'foreground' => '#9a3412',
                'background' => '#fff7ed',
                'fallback' => '#7c2d12',
                'shape' => 'square',
                'accent' => '#f97316',
                'surface' => '#ffedd5',
            ],
            'default_mosaic' => [
                'label' => __('Default Mosaic'),
                'description' => __('Plain mosaic QR with brand-aware dots only.'),
                'pattern' => 'mosaic',
                'foreground' => '#2563eb',
                'background' => '#ffffff',
                'fallback' => '#0f172a',
                'shape' => 'circle',
                'accent' => '#2563eb',
                'surface' => '#ffffff',
            ],
            'purple_badge' => [
                'label' => __('Purple badge'),
                'description' => __('Creator campaign badge.'),
                'pattern' => 'mosaic',
                'foreground' => '#7c3aed',
                'background' => '#faf5ff',
                'fallback' => '#3b0764',
                'shape' => 'circle',
                'accent' => '#a855f7',
                'surface' => '#ede9fe',
            ],
            'green_circle' => [
                'label' => __('Green square'),
                'description' => __('Green eco/event square frame.'),
                'pattern' => 'square',
                'foreground' => '#15803d',
                'background' => '#f7fee7',
                'fallback' => '#14532d',
                'shape' => 'square',
                'accent' => '#84cc16',
                'surface' => '#dcfce7',
            ],
            'blue_coupon' => [
                'label' => __('Blue square'),
                'description' => __('Blue campaign square frame.'),
                'pattern' => 'square',
                'foreground' => '#1d4ed8',
                'background' => '#eff6ff',
                'fallback' => '#1e3a8a',
                'shape' => 'square',
                'accent' => '#3b82f6',
                'surface' => '#dbeafe',
            ],
            'red_event' => [
                'label' => __('Red square'),
                'description' => __('High-energy square for event check-ins.'),
                'pattern' => 'square',
                'foreground' => '#b91c1c',
                'background' => '#fff1f2',
                'fallback' => '#7f1d1d',
                'shape' => 'square',
                'accent' => '#ef4444',
                'surface' => '#fee2e2',
            ],
            'cyan_pass' => [
                'label' => __('Cyan square'),
                'description' => __('Clean cyan square for access and tickets.'),
                'pattern' => 'square',
                'foreground' => '#0e7490',
                'background' => '#ecfeff',
                'fallback' => '#164e63',
                'shape' => 'square',
                'accent' => '#06b6d4',
                'surface' => '#cffafe',
            ],
            'amber_sale' => [
                'label' => __('Amber square'),
                'description' => __('Warm retail square for promotions.'),
                'pattern' => 'square',
                'foreground' => '#92400e',
                'background' => '#fffbeb',
                'fallback' => '#78350f',
                'shape' => 'square',
                'accent' => '#f59e0b',
                'surface' => '#fef3c7',
            ],
            'violet_orbit' => [
                'label' => __('Violet square'),
                'description' => __('Creator square frame with vivid accent.'),
                'pattern' => 'square',
                'foreground' => '#6d28d9',
                'background' => '#faf5ff',
                'fallback' => '#3b0764',
                'shape' => 'square',
                'accent' => '#8b5cf6',
                'surface' => '#ede9fe',
            ],
            'ocean_mosaic' => [
                'label' => __('Ocean mosaic'),
                'description' => __('Blue and teal image-driven QR texture.'),
                'pattern' => 'mosaic',
                'foreground' => '#0284c7',
                'background' => '#ecfeff',
                'fallback' => '#0c4a6e',
                'shape' => 'circle',
                'accent' => '#14b8a6',
                'surface' => '#e0f2fe',
            ],
            'sunset_mosaic' => [
                'label' => __('Sunset mosaic'),
                'description' => __('Warm orange and rose mosaic styling.'),
                'pattern' => 'mosaic',
                'foreground' => '#ea580c',
                'background' => '#fff7ed',
                'fallback' => '#7c2d12',
                'shape' => 'rounded',
                'accent' => '#e11d48',
                'surface' => '#ffedd5',
            ],
            'forest_mosaic' => [
                'label' => __('Forest mosaic'),
                'description' => __('Green brand-image dots for local campaigns.'),
                'pattern' => 'mosaic',
                'foreground' => '#15803d',
                'background' => '#f0fdf4',
                'fallback' => '#14532d',
                'shape' => 'circle',
                'accent' => '#65a30d',
                'surface' => '#dcfce7',
            ],
            'mono_mosaic' => [
                'label' => __('Mono mosaic'),
                'description' => __('Black and grey mosaic for strict brands.'),
                'pattern' => 'mosaic',
                'foreground' => '#111827',
                'background' => '#f8fafc',
                'fallback' => '#020617',
                'shape' => 'square',
                'accent' => '#64748b',
                'surface' => '#e5e7eb',
            ],
            'rose_mosaic' => [
                'label' => __('Rose mosaic'),
                'description' => __('Pink creator mosaic with soft background.'),
                'pattern' => 'mosaic',
                'foreground' => '#be185d',
                'background' => '#fff1f8',
                'fallback' => '#831843',
                'shape' => 'rounded',
                'accent' => '#f472b6',
                'surface' => '#fce7f3',
            ],
            'neon_mosaic' => [
                'label' => __('Neon mosaic'),
                'description' => __('Electric green and cyan dots for bold campaigns.'),
                'pattern' => 'mosaic',
                'foreground' => '#00c853',
                'background' => '#ecfeff',
                'fallback' => '#052e16',
                'shape' => 'circle',
                'accent' => '#06b6d4',
                'surface' => '#d1fae5',
            ],
            'coffee_mosaic' => [
                'label' => __('Coffee mosaic'),
                'description' => __('Warm cafe palette with rounded modules.'),
                'pattern' => 'mosaic',
                'foreground' => '#a16207',
                'background' => '#fffbeb',
                'fallback' => '#451a03',
                'shape' => 'rounded',
                'accent' => '#d97706',
                'surface' => '#fef3c7',
            ],
            'royal_mosaic' => [
                'label' => __('Royal mosaic'),
                'description' => __('Indigo and gold premium mosaic look.'),
                'pattern' => 'mosaic',
                'foreground' => '#4338ca',
                'background' => '#eef2ff',
                'fallback' => '#1e1b4b',
                'shape' => 'diamond',
                'accent' => '#facc15',
                'surface' => '#e0e7ff',
            ],
            'lime_mosaic' => [
                'label' => __('Lime mosaic'),
                'description' => __('Fresh lime dots for food and wellness campaigns.'),
                'pattern' => 'mosaic',
                'foreground' => '#65a30d',
                'background' => '#f7fee7',
                'fallback' => '#365314',
                'shape' => 'circle',
                'accent' => '#22c55e',
                'surface' => '#ecfccb',
            ],
            'ice_mosaic' => [
                'label' => __('Ice mosaic'),
                'description' => __('Soft blue-grey style for clean brands.'),
                'pattern' => 'mosaic',
                'foreground' => '#0369a1',
                'background' => '#f0f9ff',
                'fallback' => '#0f172a',
                'shape' => 'square',
                'accent' => '#7dd3fc',
                'surface' => '#e0f2fe',
            ],
            'carbon_mosaic' => [
                'label' => __('Carbon mosaic'),
                'description' => __('Dark monochrome mosaic for tech products.'),
                'pattern' => 'mosaic',
                'foreground' => '#334155',
                'background' => '#f8fafc',
                'fallback' => '#020617',
                'shape' => 'rounded',
                'accent' => '#94a3b8',
                'surface' => '#e2e8f0',
            ],
        ];
    }

    protected function ownedQrCode(): AppQrCode
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->findOrFail($this->qrCodeId);
    }

    protected function canUseQrCodes(): bool
    {
        $user = auth()->user();

        return ! $user?->plan
            || ($user?->canUsePlanFeature('qr_codes') ?? false)
            || ($user?->canUsePlanFeature('link_bio_qr_codes') ?? false);
    }

    protected function canUseQrTypeBuilders(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('qr_type_builders') ?? false);
    }

    protected function canUseBrandKit(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('brand_kit') ?? false);
    }

    protected function canUseCustomDomains(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('qr_custom_domains') ?? false);
    }

    protected function canUseUtmPresets(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('utm_presets') ?? false);
    }

    protected function canUseTrackingPixels(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('tracking_pixels') ?? false);
    }

    protected function clearUnavailableBrandFields(): void
    {
        if (! $this->canUseBrandKit()) {
            $this->form['brand_kit_id'] = '';
        }

        if (! $this->canUseCustomDomains()) {
            $this->form['custom_domain_id'] = '';
        }

        if (! $this->canUseUtmPresets()) {
            $this->form['utm_preset_id'] = '';
        }

        if (! $this->canUseTrackingPixels()) {
            $this->form['tracking_pixel_ids'] = [];
        }
    }

    protected function hasReachedDynamicRuleLimit(): bool
    {
        $limit = $this->dynamicRuleLimit();

        return $limit >= 0 && count((array) $this->form['dynamic_rules']) >= $limit;
    }

    protected function dynamicRuleLimitForValidation(): int
    {
        $limit = $this->dynamicRuleLimit();

        return $limit >= 0 ? $limit : 999;
    }

    protected function dynamicRuleLimit(): int
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
        $limit = $planOwner?->planLimit('max_dynamic_rules_per_qr', 10);

        if (! $planOwner?->plan || ! is_numeric($limit)) {
            return 10;
        }

        return (int) $limit;
    }

    protected function isAllowedQrDestination(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }

        if (! preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $matches)) {
            return false;
        }

        $scheme = strtolower($matches[1]);

        return ! in_array($scheme, ['javascript', 'data', 'vbscript'], true);
    }

    protected function previewSvg(string $value, string $foreground = '#0f172a', string $background = '#ffffff', string $ecc = 'high'): string
    {
        if ((string) data_get($this->form, 'pattern', 'square') === 'gradient_rounded') {
            $template = $this->qrTemplates()[(string) data_get($this->form, 'style_template', 'rounded_gradient')] ?? [];

            return app(GradientRoundedQrRenderer::class)->render($value, [
                'foreground_color' => $foreground,
                'accent_color' => (string) ($template['fallback'] ?? '#f97316'),
                'finder_color' => (string) ($template['accent'] ?? '#0f766e'),
                'finder_dot_color' => (string) data_get($this->form, 'fallback_dark', '#264653'),
                'background_color' => $background,
                'ecc' => $ecc,
            ]);
        }

        if ((string) data_get($this->form, 'pattern', 'square') === 'mosaic') {
            return app(BrandMosaicQrRenderer::class)->render($value, [
                'foreground_color' => (string) data_get($this->form, 'fallback_dark', $foreground),
                'accent_color' => $foreground,
                'background_color' => $background,
                'ghost_color' => $foreground,
                'shape' => (string) data_get($this->form, 'qr_shape', 'circle'),
                'ecc' => $ecc,
                'dark_min' => (int) data_get($this->form, 'dark_min', 47),
                'dark_max' => (int) data_get($this->form, 'dark_max', 105),
                'ghost_strength' => (int) data_get($this->form, 'ghost_strength', 80),
                'ghost_threshold' => (int) data_get($this->form, 'ghost_threshold', 8),
            ]);
        }

        return $this->makeQrCodeSvg($value, $foreground, $background, $ecc);
    }

    protected function makeQrCodeSvg(string $value, string $foreground = '#0f172a', string $background = '#ffffff', string $ecc = 'high'): string
    {
        [$foregroundR, $foregroundG, $foregroundB] = $this->hexToRgb($foreground, [15, 23, 42]);
        [$backgroundR, $backgroundG, $backgroundB] = $this->hexToRgb($background, [255, 255, 255]);
        $renderer = new ImageRenderer(
            new RendererStyle(
                180,
                4,
                null,
                null,
                Fill::uniformColor(new Rgb($backgroundR, $backgroundG, $backgroundB), new Rgb($foregroundR, $foregroundG, $foregroundB))
            ),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($value, ecLevel: $this->errorCorrectionLevel($ecc));
    }

    protected function errorCorrectionLevel(string $ecc): ErrorCorrectionLevel
    {
        return match ($ecc) {
            'low' => ErrorCorrectionLevel::L(),
            'medium' => ErrorCorrectionLevel::M(),
            'quartile' => ErrorCorrectionLevel::Q(),
            default => ErrorCorrectionLevel::H(),
        };
    }

    protected function hexToRgb(string $hex, array $fallback): array
    {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#'.$hex;

        if (! preg_match('/^#[0-9a-f]{6}$/', $hex)) {
            return $fallback;
        }

        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }
}
