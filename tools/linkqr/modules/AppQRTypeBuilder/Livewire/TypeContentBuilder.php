<?php

namespace Modules\AppQRTypeBuilder\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('QR Type Content')]
class TypeContentBuilder extends Component
{
    public int $qrCodeId;

    public array $content = [];

    public string $publicTemplate = 'split_showcase';

    public function mount(AppQrCode $qrCode): void
    {
        abort_unless($this->canUseQrTypeBuilders(), 403);
        abort_unless((int) $qrCode->owner_user_id === TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()), 404);
        abort_unless($this->schemaFor($qrCode->type) !== [], 404);

        $this->qrCodeId = (int) $qrCode->id;
        $this->content = array_replace_recursive(
            $this->defaultsFor($qrCode->type),
            (array) data_get($qrCode->settings, 'type_content', [])
        );
        $this->publicTemplate = (string) data_get($qrCode->settings, 'public_template', 'split_showcase');
    }

    public function save(): void
    {
        abort_unless($this->canUseQrTypeBuilders(), 403);

        $qrCode = $this->ownedQrCode();
        $settings = (array) ($qrCode->settings ?: []);
        $settings['type_content'] = $this->content;

        if ($this->supportsPublicPageTemplate($qrCode->type)) {
            $validator = validator(
                ['public_template' => $this->publicTemplate],
                ['public_template' => ['required', 'in:'.implode(',', array_keys($this->publicTemplates()))]]
            );

            if ($validator->fails()) {
                $this->publicTemplate = 'split_showcase';
            }

            $settings['public_template'] = $this->publicTemplate;
        } else {
            unset($settings['public_template']);
        }

        $destination = $this->destinationFor($qrCode->type);

        $qrCode->update([
            'settings' => $settings,
            'destination_url' => $destination ?: $qrCode->destination_url,
            'name' => trim((string) data_get($this->content, 'title', '')) ?: $qrCode->name,
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('QR type content saved.'));
    }

    public function addListItem(string $listKey): void
    {
        $defaults = match ($listKey) {
            'links' => ['label' => '', 'url' => ''],
            'products' => ['name' => '', 'price' => '', 'image_url' => '', 'url' => '', 'description' => ''],
            'fields' => ['label' => '', 'type' => 'text', 'required' => false],
            'categories' => ['label' => '', 'url' => ''],
            'allergens' => ['label' => '', 'url' => ''],
            'reviews' => ['label' => '', 'url' => ''],
            default => ['label' => '', 'value' => ''],
        };

        $items = (array) data_get($this->content, $listKey, []);
        $items[] = $defaults;
        data_set($this->content, $listKey, $items);
    }

    public function removeListItem(string $listKey, int $index): void
    {
        $items = array_values((array) data_get($this->content, $listKey, []));
        unset($items[$index]);
        data_set($this->content, $listKey, array_values($items));
    }

    public function render(): View
    {
        abort_unless($this->canUseQrTypeBuilders(), 403);

        $qrCode = $this->ownedQrCode();
        $typeMeta = collect(AppQrCode::typeCatalog())->firstWhere('key', $qrCode->type) ?: [
            'label' => str_replace('_', ' ', $qrCode->type),
            'kind' => $qrCode->short_code ? __('dynamic') : __('static'),
            'icon' => 'fa-qrcode',
            'description' => '',
        ];

        $publicTemplates = $this->supportsPublicPageTemplate($qrCode->type) ? $this->publicTemplates() : [];

        return view('appqrtypebuilder::builder', [
            'qrCode' => $qrCode,
            'typeMeta' => $typeMeta,
            'schema' => $this->schemaFor($qrCode->type),
            'preview' => $this->previewFor($qrCode->type),
            'publicTemplates' => $publicTemplates,
            'selectedPublicTemplate' => $publicTemplates[$this->publicTemplate] ?? ($publicTemplates['split_showcase'] ?? null),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('QR Type Content')]);
    }

    protected function supportsPublicPageTemplate(string $type): bool
    {
        return in_array($type, [
            'business_profile',
            'website_builder',
            'vcard_plus',
            'vcard',
            'lead_form',
            'product_catalogue',
            'restaurant_menu',
            'app_download',
            'resume_qr_code',
            'file_upload',
            'event',
            'booking',
            'donation',
        ], true);
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

    protected function ownedQrCode(): AppQrCode
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->findOrFail($this->qrCodeId);
    }

    protected function canUseQrTypeBuilders(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('qr_type_builders') ?? false);
    }

    protected function schemaFor(string $type): array
    {
        return match ($type) {
            'bio_links' => [
                'help' => __('Connect this QR to a Link Bio page URL. Full design, tracking, domains, and redirects are managed in the QR builder.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Bio page name'), 'type' => 'text'],
                    ['key' => 'url', 'label' => __('Link Bio URL'), 'type' => 'url', 'span' => 2],
                ],
            ],
            'dynamic_url' => [
                'help' => __('Redirect to a destination URL. You can change it later and keep the printed QR working.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Campaign name'), 'type' => 'text'],
                    ['key' => 'url', 'label' => __('Destination URL'), 'type' => 'url', 'span' => 2],
                    ['key' => 'utm_source', 'label' => __('UTM source'), 'type' => 'text'],
                    ['key' => 'utm_medium', 'label' => __('UTM medium'), 'type' => 'text'],
                    ['key' => 'utm_campaign', 'label' => __('UTM campaign'), 'type' => 'text', 'span' => 2],
                ],
            ],
            'business_profile' => [
                'help' => __('Create a public business profile with contact details, address, and opening hours.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Business name'), 'type' => 'text'],
                    ['key' => 'category', 'label' => __('Business category'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'phone', 'label' => __('Phone'), 'type' => 'text'],
                    ['key' => 'email', 'label' => __('Email'), 'type' => 'email'],
                    ['key' => 'website', 'label' => __('Website'), 'type' => 'url'],
                    ['key' => 'address', 'label' => __('Address'), 'type' => 'textarea'],
                    ['key' => 'opening_hours', 'label' => __('Opening hours'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'business_review', 'google_review' => [
                'help' => __('Send customers to the right review flow and show a friendly review request page.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Business name'), 'type' => 'text'],
                    ['key' => 'review_url', 'label' => __('Review URL'), 'type' => 'url'],
                    ['key' => 'headline', 'label' => __('Headline'), 'type' => 'text'],
                    ['key' => 'rating_threshold', 'label' => __('Public review threshold'), 'type' => 'select', 'options' => ['4+', '5 only', 'All ratings']],
                    ['key' => 'message', 'label' => __('Review request message'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'thank_you_message', 'label' => __('Thank you message'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'website_builder' => [
                'help' => __('Build a simple mobile landing page for campaigns, launches, and local businesses.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Page title'), 'type' => 'text'],
                    ['key' => 'headline', 'label' => __('Headline'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Intro copy'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'hero_image_url', 'label' => __('Hero image'), 'type' => 'image', 'span' => 2],
                    ['key' => 'cta_label', 'label' => __('CTA label'), 'type' => 'text'],
                    ['key' => 'cta_url', 'label' => __('CTA URL'), 'type' => 'url', 'span' => 2],
                ],
                'lists' => ['links'],
            ],
            'vcard_plus', 'vcard' => [
                'help' => __('Package contact information into a QR-ready business card.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Full name'), 'type' => 'text'],
                    ['key' => 'company', 'label' => __('Company'), 'type' => 'text'],
                    ['key' => 'job_title', 'label' => __('Job title'), 'type' => 'text'],
                    ['key' => 'phone', 'label' => __('Phone'), 'type' => 'text'],
                    ['key' => 'email', 'label' => __('Email'), 'type' => 'email'],
                    ['key' => 'website', 'label' => __('Website'), 'type' => 'url'],
                    ['key' => 'address', 'label' => __('Address'), 'type' => 'textarea', 'span' => 2],
                ],
                'lists' => ['links'],
            ],
            'lead_form' => [
                'help' => __('Capture leads from scans and send submissions to email or webhook.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Form title'), 'type' => 'text'],
                    ['key' => 'submit_label', 'label' => __('Submit button'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'notify_email', 'label' => __('Notify email'), 'type' => 'email'],
                    ['key' => 'webhook_url', 'label' => __('Webhook URL'), 'type' => 'url'],
                    ['key' => 'thank_you_message', 'label' => __('Thank you message'), 'type' => 'textarea', 'span' => 2],
                ],
                'lists' => ['fields'],
            ],
            'product_catalogue' => [
                'help' => __('Show products, prices, product URLs, and a purchase/contact CTA.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Catalogue name'), 'type' => 'text'],
                    ['key' => 'show_prices', 'label' => __('Show prices'), 'type' => 'select', 'options' => ['yes', 'no']],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'cta_label', 'label' => __('CTA label'), 'type' => 'text'],
                    ['key' => 'cta_url', 'label' => __('CTA URL'), 'type' => 'url'],
                    ['key' => 'whatsapp_number', 'label' => __('WhatsApp order number'), 'type' => 'text', 'span' => 2],
                ],
                'lists' => ['products'],
            ],
            'restaurant_menu' => [
                'help' => __('Create a restaurant menu landing page with categories, items, allergens, WhatsApp ordering, and review links.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Restaurant name'), 'type' => 'text'],
                    ['key' => 'subtitle', 'label' => __('Menu subtitle'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Intro text'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'logo_url', 'label' => __('Logo'), 'type' => 'image'],
                    ['key' => 'background_image_url', 'label' => __('Background image'), 'type' => 'image'],
                    ['key' => 'show_prices', 'label' => __('Show prices'), 'type' => 'select', 'options' => ['yes', 'no']],
                    ['key' => 'line_separator', 'label' => __('Line separator'), 'type' => 'select', 'options' => ['enabled', 'disabled']],
                    ['key' => 'language', 'label' => __('QR page language'), 'type' => 'select', 'options' => ['English', 'Spanish', 'Japanese', 'Chinese Traditional', 'Vietnamese']],
                    ['key' => 'whatsapp_enabled', 'label' => __('WhatsApp ordering'), 'type' => 'select', 'options' => ['enabled', 'disabled']],
                    ['key' => 'whatsapp_number', 'label' => __('WhatsApp order number'), 'type' => 'text'],
                    ['key' => 'order_type', 'label' => __('Order type'), 'type' => 'select', 'options' => ['delivery', 'table']],
                    ['key' => 'payment_type', 'label' => __('Payment type'), 'type' => 'select', 'options' => ['none', 'UPI', 'PayPal']],
                    ['key' => 'terms', 'label' => __('Terms and conditions'), 'type' => 'textarea', 'span' => 2],
                ],
                'lists' => ['categories', 'products', 'allergens', 'reviews'],
            ],
            'app_download' => [
                'help' => __('Create a smart app download QR with separate iOS, Android, and fallback links.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('App name'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'ios_url', 'label' => __('App Store URL'), 'type' => 'url'],
                    ['key' => 'android_url', 'label' => __('Google Play URL'), 'type' => 'url'],
                    ['key' => 'fallback_url', 'label' => __('Fallback URL'), 'type' => 'url', 'span' => 2],
                ],
            ],
            'resume_qr_code' => [
                'help' => __('Share a professional resume profile, portfolio link, and downloadable CV.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Full name'), 'type' => 'text'],
                    ['key' => 'job_title', 'label' => __('Target role'), 'type' => 'text'],
                    ['key' => 'summary', 'label' => __('Professional summary'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'portfolio_url', 'label' => __('Portfolio URL'), 'type' => 'url'],
                    ['key' => 'resume_file_url', 'label' => __('Resume file URL'), 'type' => 'url'],
                ],
            ],
            'file_upload' => [
                'help' => __('Upload or choose a hosted file, then share it through a dynamic QR content page.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('File title'), 'type' => 'text'],
                    ['key' => 'file_url', 'label' => __('File'), 'type' => 'file'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'button_label', 'label' => __('Button label'), 'type' => 'text'],
                    ['key' => 'access_note', 'label' => __('Access note'), 'type' => 'text'],
                ],
            ],
            'event' => [
                'help' => __('Publish event information with RSVP, calendar, and location details.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Event name'), 'type' => 'text'],
                    ['key' => 'starts_at', 'label' => __('Starts at'), 'type' => 'text'],
                    ['key' => 'ends_at', 'label' => __('Ends at'), 'type' => 'text'],
                    ['key' => 'location', 'label' => __('Location'), 'type' => 'text'],
                    ['key' => 'rsvp_url', 'label' => __('RSVP URL'), 'type' => 'url'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea'],
                ],
            ],
            'booking' => [
                'help' => __('Let customers open a booking page or appointment request.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Booking name'), 'type' => 'text'],
                    ['key' => 'booking_url', 'label' => __('Booking URL'), 'type' => 'url'],
                    ['key' => 'service_name', 'label' => __('Service name'), 'type' => 'text'],
                    ['key' => 'duration', 'label' => __('Duration'), 'type' => 'text'],
                    ['key' => 'instructions', 'label' => __('Booking instructions'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'static_url' => [
                'help' => __('Encode fixed text or URL directly into the QR. Printed QR cannot be redirected later.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'url', 'label' => __('Static URL / Text'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'email', 'email_dynamic' => [
                'help' => __('Open the email app with recipient, subject, and body prefilled.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'email', 'label' => __('Recipient email'), 'type' => 'email'],
                    ['key' => 'subject', 'label' => __('Subject'), 'type' => 'text', 'span' => 2],
                    ['key' => 'body', 'label' => __('Body'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'sms', 'sms_dynamic' => [
                'help' => __('Open the SMS app with phone number and message prefilled.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'phone', 'label' => __('Phone number'), 'type' => 'text'],
                    ['key' => 'message', 'label' => __('Message'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'call' => [
                'help' => __('Start a phone call from the scan result.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'phone', 'label' => __('Phone number'), 'type' => 'text'],
                ],
            ],
            'wifi' => [
                'help' => __('Encode Wi-Fi network credentials.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Network label'), 'type' => 'text'],
                    ['key' => 'ssid', 'label' => __('SSID'), 'type' => 'text'],
                    ['key' => 'password', 'label' => __('Password'), 'type' => 'text'],
                    ['key' => 'encryption', 'label' => __('Encryption'), 'type' => 'select', 'options' => ['WPA', 'WEP', 'nopass']],
                    ['key' => 'hidden', 'label' => __('Hidden network'), 'type' => 'select', 'options' => ['no', 'yes']],
                ],
            ],
            'whatsapp' => [
                'help' => __('Open WhatsApp with a prefilled message.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'phone', 'label' => __('WhatsApp number'), 'type' => 'text'],
                    ['key' => 'message', 'label' => __('Prefilled message'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'facetime' => [
                'help' => __('Open FaceTime audio or video.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'contact', 'label' => __('Phone or Apple ID'), 'type' => 'text'],
                    ['key' => 'mode', 'label' => __('Mode'), 'type' => 'select', 'options' => ['video', 'audio']],
                ],
            ],
            'location' => [
                'help' => __('Open a map app using coordinates or a maps URL.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Place name'), 'type' => 'text'],
                    ['key' => 'maps_app', 'label' => __('Preferred app'), 'type' => 'select', 'options' => ['device default', 'Google Maps', 'Waze']],
                    ['key' => 'latitude', 'label' => __('Latitude'), 'type' => 'text'],
                    ['key' => 'longitude', 'label' => __('Longitude'), 'type' => 'text'],
                    ['key' => 'maps_url', 'label' => __('Maps URL'), 'type' => 'url', 'span' => 2],
                    ['key' => 'address', 'label' => __('Address'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            'crypto' => [
                'help' => __('Encode a crypto wallet payment request.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Payment name'), 'type' => 'text'],
                    ['key' => 'coin', 'label' => __('Coin'), 'type' => 'select', 'options' => ['bitcoin', 'ethereum', 'litecoin', 'dash', 'bitcoin-cash']],
                    ['key' => 'wallet_address', 'label' => __('Wallet address'), 'type' => 'text', 'span' => 2],
                    ['key' => 'amount', 'label' => __('Amount'), 'type' => 'text'],
                    ['key' => 'message', 'label' => __('Message'), 'type' => 'text'],
                ],
            ],
            'donation' => [
                'help' => __('Configure Stripe donation details, currency, preset amounts, donor options, and the public donation page.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Campaign title'), 'type' => 'text'],
                    ['key' => 'button_text', 'label' => __('Button text'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'stripe_publishable_key', 'label' => __('Stripe Publishable Key'), 'type' => 'password'],
                    ['key' => 'stripe_secret_key', 'label' => __('Stripe Secret Key'), 'type' => 'password'],
                    ['key' => 'currency', 'label' => __('Currency'), 'type' => 'select', 'options' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'JPY', 'SGD', 'HKD', 'BRL']],
                    ['key' => 'amount_1', 'label' => __('Amount 1'), 'type' => 'text'],
                    ['key' => 'amount_2', 'label' => __('Amount 2'), 'type' => 'text'],
                    ['key' => 'amount_3', 'label' => __('Amount 3'), 'type' => 'text'],
                    ['key' => 'custom_amount_enabled', 'label' => __('Custom amount'), 'type' => 'select', 'options' => ['enabled', 'disabled']],
                    ['key' => 'donor_note_enabled', 'label' => __('Donor note'), 'type' => 'select', 'options' => ['enabled', 'disabled']],
                    ['key' => 'recurring_enabled', 'label' => __('Recurring donation'), 'type' => 'select', 'options' => ['disabled', 'enabled']],
                    ['key' => 'thank_you_message', 'label' => __('Thank you message'), 'type' => 'textarea', 'span' => 2],
                    ['key' => 'background_color', 'label' => __('Background color'), 'type' => 'text'],
                    ['key' => 'accent_color', 'label' => __('Accent color'), 'type' => 'text'],
                    ['key' => 'text_color', 'label' => __('Text color'), 'type' => 'text'],
                ],
            ],
            'paypal' => [
                'help' => __('Send users to a PayPal payment or donation link.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Payment name'), 'type' => 'text'],
                    ['key' => 'paypal_url', 'label' => __('PayPal payment URL'), 'type' => 'url'],
                    ['key' => 'amount', 'label' => __('Amount'), 'type' => 'text'],
                    ['key' => 'currency', 'label' => __('Currency'), 'type' => 'text'],
                ],
            ],
            'upi_static', 'upi_dynamic' => [
                'help' => __('Create a UPI payment QR with payee, amount, and transaction note.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Payment name'), 'type' => 'text'],
                    ['key' => 'upi_id', 'label' => __('UPI ID'), 'type' => 'text'],
                    ['key' => 'payee_name', 'label' => __('Payee name'), 'type' => 'text'],
                    ['key' => 'amount', 'label' => __('Amount'), 'type' => 'text'],
                    ['key' => 'note', 'label' => __('Payment note'), 'type' => 'text', 'span' => 2],
                ],
            ],
            'zoom' => [
                'help' => __('Open a Zoom meeting link with optional meeting metadata.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Meeting name'), 'type' => 'text'],
                    ['key' => 'zoom_url', 'label' => __('Zoom URL'), 'type' => 'url'],
                    ['key' => 'meeting_id', 'label' => __('Meeting ID'), 'type' => 'text'],
                    ['key' => 'passcode', 'label' => __('Passcode'), 'type' => 'text'],
                ],
            ],
            'telegram' => [
                'help' => __('Open a Telegram username, channel, or invite link.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'telegram_url', 'label' => __('Telegram URL'), 'type' => 'url'],
                    ['key' => 'username', 'label' => __('Username'), 'type' => 'text'],
                ],
            ],
            'brazilian_pix' => [
                'help' => __('Encode Brazilian PIX payment details.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Payment name'), 'type' => 'text'],
                    ['key' => 'pix_key', 'label' => __('PIX key'), 'type' => 'text'],
                    ['key' => 'merchant_name', 'label' => __('Merchant name'), 'type' => 'text'],
                    ['key' => 'city', 'label' => __('City'), 'type' => 'text'],
                    ['key' => 'amount', 'label' => __('Amount'), 'type' => 'text'],
                    ['key' => 'description', 'label' => __('Description'), 'type' => 'textarea'],
                ],
            ],
            'messenger' => [
                'help' => __('Open Facebook Messenger from a page username or m.me URL.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'page_username', 'label' => __('Page username'), 'type' => 'text'],
                    ['key' => 'messenger_url', 'label' => __('Messenger URL'), 'type' => 'url'],
                ],
            ],
            'viber' => [
                'help' => __('Open a Viber chat or call.'),
                'fields' => [
                    ['key' => 'title', 'label' => __('Name'), 'type' => 'text'],
                    ['key' => 'phone', 'label' => __('Viber phone'), 'type' => 'text'],
                    ['key' => 'message', 'label' => __('Message'), 'type' => 'textarea', 'span' => 2],
                ],
            ],
            default => [],
        };
    }

    protected function defaultsFor(string $type): array
    {
        $schema = $this->schemaFor($type);
        $fields = collect((array) data_get($schema, 'fields', []))
            ->mapWithKeys(fn (array $field): array => [$field['key'] => $field['options'][0] ?? ''])
            ->all();

        foreach ((array) data_get($schema, 'lists', []) as $listKey) {
            $fields[$listKey] = match ($listKey) {
                'fields' => [
                    ['label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['label' => __('Email'), 'type' => 'email', 'required' => false],
                ],
                'products' => [
                    ['name' => __('Sample product'), 'price' => '', 'image_url' => '', 'url' => '', 'description' => ''],
                ],
                'categories' => [
                    ['label' => __('Main dishes'), 'url' => ''],
                ],
                'allergens' => [
                    ['label' => __('Gluten'), 'url' => ''],
                ],
                'reviews' => [
                    ['label' => __('Google Review'), 'url' => ''],
                ],
                default => [
                    ['label' => __('Website'), 'url' => ''],
                ],
            };
        }

        return $fields;
    }

    protected function destinationFor(string $type): ?string
    {
        return match ($type) {
            'bio_links', 'dynamic_url', 'static_url' => $this->clean('url'),
            'business_profile', 'vcard_plus', 'vcard' => $this->clean('website') ?: $this->vcardPayload(),
            'business_review', 'google_review' => $this->clean('review_url'),
            'website_builder' => $this->clean('cta_url') ?: $this->clean('hero_image_url'),
            'lead_form' => $this->clean('webhook_url'),
            'product_catalogue' => $this->clean('cta_url'),
            'restaurant_menu' => $this->whatsappPayload() ?: $this->clean('logo_url') ?: $this->clean('background_image_url'),
            'app_download' => $this->clean('fallback_url') ?: $this->clean('ios_url') ?: $this->clean('android_url'),
            'resume_qr_code' => $this->clean('portfolio_url') ?: $this->clean('resume_file_url'),
            'file_upload' => $this->clean('file_url'),
            'event' => $this->clean('rsvp_url'),
            'booking' => $this->clean('booking_url'),
            'location' => $this->clean('maps_url') ?: $this->geoPayload(),
            'paypal' => $this->clean('paypal_url'),
            'email', 'email_dynamic' => $this->emailPayload(),
            'sms', 'sms_dynamic' => $this->smsPayload(),
            'call' => $this->clean('phone') ? 'tel:'.$this->clean('phone') : null,
            'whatsapp' => $this->whatsappPayload(),
            'wifi' => $this->wifiPayload(),
            'facetime' => $this->facetimePayload(),
            'crypto' => $this->cryptoPayload(),
            'donation' => null,
            'upi_static', 'upi_dynamic' => $this->upiPayload(),
            'zoom' => $this->clean('zoom_url'),
            'telegram' => $this->clean('telegram_url') ?: ($this->clean('username') ? 'https://t.me/'.ltrim($this->clean('username'), '@') : null),
            'brazilian_pix' => $this->clean('pix_key'),
            'messenger' => $this->clean('messenger_url') ?: ($this->clean('page_username') ? 'https://m.me/'.$this->clean('page_username') : null),
            'viber' => $this->viberPayload(),
            default => null,
        };
    }

    protected function previewFor(string $type): array
    {
        $body = Arr::first([
            data_get($this->content, 'description'),
            data_get($this->content, 'message'),
            data_get($this->content, 'summary'),
            data_get($this->content, 'address'),
            data_get($this->content, 'url'),
            data_get($this->content, 'website'),
            data_get($this->content, 'file_url'),
        ], fn ($value): bool => trim((string) $value) !== '');

        return [
            'title' => (string) (data_get($this->content, 'title') ?: str_replace('_', ' ', $type)),
            'body' => (string) $body,
            'destination' => $this->destinationFor($type),
        ];
    }

    protected function clean(string $key): ?string
    {
        $value = trim((string) data_get($this->content, $key, ''));

        return $value !== '' ? $value : null;
    }

    protected function emailPayload(): ?string
    {
        $email = $this->clean('email');

        if (! $email) {
            return null;
        }

        return 'mailto:'.$email.'?subject='.rawurlencode((string) data_get($this->content, 'subject')).'&body='.rawurlencode((string) data_get($this->content, 'body'));
    }

    protected function smsPayload(): ?string
    {
        $phone = $this->clean('phone');

        return $phone ? 'sms:'.$phone.'?body='.rawurlencode((string) data_get($this->content, 'message')) : null;
    }

    protected function whatsappPayload(): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) (data_get($this->content, 'phone') ?: data_get($this->content, 'whatsapp_number')));

        return $phone ? 'https://wa.me/'.$phone.'?text='.rawurlencode((string) data_get($this->content, 'message')) : null;
    }

    protected function viberPayload(): ?string
    {
        $phone = preg_replace('/[^\d+]+/', '', $this->clean('phone'));

        return $phone ? 'viber://chat?number='.rawurlencode($phone) : null;
    }

    protected function wifiPayload(): ?string
    {
        $ssid = $this->clean('ssid');

        if (! $ssid) {
            return null;
        }

        return 'WIFI:T:'.data_get($this->content, 'encryption', 'WPA').';S:'.$ssid.';P:'.data_get($this->content, 'password').';H:'.(data_get($this->content, 'hidden') === 'yes' ? 'true' : 'false').';;';
    }

    protected function facetimePayload(): ?string
    {
        $contact = $this->clean('contact');

        if (! $contact) {
            return null;
        }

        return data_get($this->content, 'mode') === 'audio' ? 'facetime-audio:'.$contact : 'facetime:'.$contact;
    }

    protected function geoPayload(): ?string
    {
        $latitude = $this->clean('latitude');
        $longitude = $this->clean('longitude');

        return $latitude && $longitude ? 'geo:'.$latitude.','.$longitude : null;
    }

    protected function cryptoPayload(): ?string
    {
        $coin = (string) data_get($this->content, 'coin', 'bitcoin');
        $address = $this->clean('wallet_address');

        if (! $address) {
            return null;
        }

        $query = http_build_query(array_filter([
            'amount' => $this->clean('amount'),
            'message' => $this->clean('message'),
        ]));

        return $coin.':'.$address.($query ? '?'.$query : '');
    }

    protected function upiPayload(): ?string
    {
        $upiId = $this->clean('upi_id');

        if (! $upiId) {
            return null;
        }

        return 'upi://pay?'.http_build_query(array_filter([
            'pa' => $upiId,
            'pn' => $this->clean('payee_name'),
            'am' => $this->clean('amount'),
            'tn' => $this->clean('note'),
        ]));
    }

    protected function vcardPayload(): ?string
    {
        $name = $this->clean('title');

        if (! $name) {
            return null;
        }

        return "BEGIN:VCARD\nVERSION:3.0\nFN:".$name."\nORG:".(string) data_get($this->content, 'company')."\nTITLE:".(string) data_get($this->content, 'job_title')."\nTEL:".(string) data_get($this->content, 'phone')."\nEMAIL:".(string) data_get($this->content, 'email')."\nADR:".(string) data_get($this->content, 'address')."\nEND:VCARD";
    }
}
