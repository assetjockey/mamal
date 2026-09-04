<?php

namespace Modules\AppQRCodes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\AppBrandKit\Support\BrandOperations;

class AppQrCode extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'type',
        'name',
        'status',
        'destination_url',
        'short_code',
        'short_domain',
        'foreground_color',
        'background_color',
        'pattern',
        'logo_url',
        'scans_count',
        'last_scanned_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'scans_count' => 'integer',
            'last_scanned_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function shortUrl(): ?string
    {
        if (! $this->short_code) {
            return null;
        }

        $fallback = route('qr-codes.public.show', ['code' => $this->short_code]);
        $brandOps = app(BrandOperations::class);
        $domain = $brandOps->verifiedDomain((int) data_get($this->settings, 'custom_domain_id'), (int) $this->owner_user_id)
            ?: $brandOps->defaultQrDomain((int) $this->owner_user_id);

        return $brandOps->customUrl($domain, 'q/'.$this->short_code, $fallback);
    }

    public static function uniqueShortCode(string $seed = ''): string
    {
        $base = Str::lower(Str::slug($seed, ''));
        $base = $base !== '' ? Str::limit($base, 18, '') : Str::lower(Str::random(8));
        $code = $base;
        $counter = 2;

        while (self::query()->where('short_code', $code)->exists()) {
            $code = Str::limit($base, 16, '').$counter;
            $counter++;
        }

        return $code;
    }

    public static function typeCatalog(): array
    {
        return [
            ['key' => 'dynamic_url', 'label' => __('Dynamic URL'), 'kind' => __('dynamic'), 'icon' => 'fa-globe', 'description' => __('Change destination after printing.')],
            ['key' => 'bio_links', 'label' => __('Bio Links'), 'kind' => __('dynamic'), 'icon' => 'fa-list', 'description' => __('Connect a QR code to a Link Bio page.')],
            ['key' => 'business_profile', 'label' => __('Business Profile'), 'kind' => __('dynamic'), 'icon' => 'fa-id-card', 'description' => __('Company profile with contact and map.')],
            ['key' => 'business_review', 'label' => __('Business Review'), 'kind' => __('dynamic'), 'icon' => 'fa-star', 'description' => __('Collect Google or custom reviews.')],
            ['key' => 'website_builder', 'label' => __('Website Builder'), 'kind' => __('dynamic'), 'icon' => 'fa-globe-pointer', 'description' => __('Simple landing page with sections and CTA.')],
            ['key' => 'vcard_plus', 'label' => __('vCard Plus'), 'kind' => __('dynamic'), 'icon' => 'fa-address-card', 'description' => __('Dynamic business card with profile and links.')],
            ['key' => 'lead_form', 'label' => __('Lead Form'), 'kind' => __('dynamic'), 'icon' => 'fa-clipboard-list', 'description' => __('Capture leads from scans.')],
            ['key' => 'restaurant_menu', 'label' => __('Restaurant Menu'), 'kind' => __('dynamic'), 'icon' => 'fa-utensils', 'description' => __('Digital menu and WhatsApp ordering.')],
            ['key' => 'product_catalogue', 'label' => __('Product Catalogue'), 'kind' => __('dynamic'), 'icon' => 'fa-box-open', 'description' => __('Show products, prices, and order CTA.')],
            ['key' => 'app_download', 'label' => __('App Download'), 'kind' => __('dynamic'), 'icon' => 'fa-mobile-screen-button', 'description' => __('Route users to App Store or Google Play.')],
            ['key' => 'google_review', 'label' => __('Google Review'), 'kind' => __('dynamic'), 'icon' => 'fa-g', 'description' => __('Send customers to a Google review link.')],
            ['key' => 'resume_qr_code', 'label' => __('Resume QR Code'), 'kind' => __('dynamic'), 'icon' => 'fa-file-user', 'description' => __('Share a resume profile or downloadable CV.')],
            ['key' => 'file_upload', 'label' => __('File Upload'), 'kind' => __('dynamic'), 'icon' => 'fa-file-arrow-up', 'description' => __('Share PDF, image, or downloadable files.')],
            ['key' => 'event', 'label' => __('Event'), 'kind' => __('dynamic'), 'icon' => 'fa-calendar-days', 'description' => __('Event details, RSVP, and calendar links.')],
            ['key' => 'booking', 'label' => __('Booking'), 'kind' => __('dynamic'), 'icon' => 'fa-calendar-check', 'description' => __('Appointment or reservation QR.')],
            ['key' => 'static_url', 'label' => __('Static Text / URL'), 'kind' => __('static'), 'icon' => 'fa-link', 'description' => __('Simple QR without tracking redirect.')],
            ['key' => 'vcard', 'label' => __('VCard'), 'kind' => __('static'), 'icon' => 'fa-address-card', 'description' => __('Store contact details directly in the QR.')],
            ['key' => 'email_dynamic', 'label' => __('Email (Dynamic)'), 'kind' => __('dynamic'), 'icon' => 'fa-envelope-open-text', 'description' => __('Editable email QR with analytics.')],
            ['key' => 'email', 'label' => __('Email'), 'kind' => __('static'), 'icon' => 'fa-envelope', 'description' => __('Prefill recipient, subject, and body.')],
            ['key' => 'sms', 'label' => __('SMS'), 'kind' => __('static'), 'icon' => 'fa-message', 'description' => __('Prefill phone number and message.')],
            ['key' => 'sms_dynamic', 'label' => __('SMS (Dynamic)'), 'kind' => __('dynamic'), 'icon' => 'fa-message-sms', 'description' => __('Editable SMS QR with analytics.')],
            ['key' => 'call', 'label' => __('Call'), 'kind' => __('static'), 'icon' => 'fa-phone', 'description' => __('Start a phone call after scanning.')],
            ['key' => 'wifi', 'label' => __('WIFI'), 'kind' => __('static'), 'icon' => 'fa-wifi', 'description' => __('Share secure Wi-Fi credentials.')],
            ['key' => 'whatsapp', 'label' => __('WhatsApp'), 'kind' => __('dynamic'), 'icon' => 'fa-message-lines', 'description' => __('Start chat or order flow.')],
            ['key' => 'facetime', 'label' => __('FaceTime'), 'kind' => __('static'), 'icon' => 'fa-video', 'description' => __('Start FaceTime audio or video.')],
            ['key' => 'location', 'label' => __('Location'), 'kind' => __('static'), 'icon' => 'fa-location-dot', 'description' => __('Open Maps, Waze, or device maps.')],
            ['key' => 'crypto', 'label' => __('Crypto'), 'kind' => __('static'), 'icon' => 'fa-bitcoin-sign', 'description' => __('Bitcoin, Ethereum, or other wallet address.')],
            ['key' => 'donation', 'label' => __('Donation'), 'kind' => __('dynamic'), 'icon' => 'fa-hand-holding-heart', 'description' => __('Collect donations with Stripe, preset amounts, and donor notes.')],
            ['key' => 'paypal', 'label' => __('PayPal'), 'kind' => __('dynamic'), 'icon' => 'fa-credit-card', 'description' => __('Payment or donation QR.')],
            ['key' => 'upi_static', 'label' => __('UPI (Static)'), 'kind' => __('static'), 'icon' => 'fa-money-bill-transfer', 'description' => __('Static UPI payment QR.')],
            ['key' => 'upi_dynamic', 'label' => __('UPI (Dynamic)'), 'kind' => __('dynamic'), 'icon' => 'fa-money-bill-transfer', 'description' => __('Editable UPI payment QR.')],
            ['key' => 'zoom', 'label' => __('Zoom'), 'kind' => __('static'), 'icon' => 'fa-video-plus', 'description' => __('Open a Zoom meeting link.')],
            ['key' => 'telegram', 'label' => __('Telegram'), 'kind' => __('static'), 'icon' => 'fa-paper-plane', 'description' => __('Open Telegram chat or channel.')],
            ['key' => 'brazilian_pix', 'label' => __('Brazilian PIX'), 'kind' => __('static'), 'icon' => 'fa-diamond', 'description' => __('Brazilian PIX payment payload.')],
            ['key' => 'messenger', 'label' => __('Messenger'), 'kind' => __('static'), 'icon' => 'fa-comment', 'description' => __('Open a Facebook Messenger conversation.')],
            ['key' => 'viber', 'label' => __('Viber Chat'), 'kind' => __('static'), 'icon' => 'fa-phone-volume', 'description' => __('Open Viber chat or call.')],
        ];
    }
}
