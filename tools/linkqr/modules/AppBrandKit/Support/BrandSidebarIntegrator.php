<?php

namespace Modules\AppBrandKit\Support;

use Modules\AdminSettings\Support\OptionStore;

class BrandSidebarIntegrator
{
    public const ITEMS = [
        ['key' => 'portal.brand.kit', 'label' => 'Brand Kit'],
        ['key' => 'portal.brand.domains', 'label' => 'Custom Domains'],
        ['key' => 'portal.brand.tracking-pixels', 'label' => 'Tracking Pixels'],
        ['key' => 'portal.brand.utm-presets', 'label' => 'UTM Presets'],
    ];

    public static function registerSharedItem(array $item, int $order): void
    {
        register_user_sidebar_section('brand', 'Brand', 31);

        register_user_sidebar_item('brand', array_merge($item, [
            'order' => $order,
        ]));
    }

    public static function sync(): void
    {
        $options = app(OptionStore::class);
        $stored = self::storedOverrides($options);

        if (! is_array($stored['user']['sections'] ?? null)) {
            return;
        }

        $brandKeys = array_column(self::ITEMS, 'key');
        $brandSection = [
            'key' => 'brand',
            'label' => 'Brand',
            'items' => self::ITEMS,
        ];

        $sections = collect($stored['user']['sections'])
            ->filter(fn ($section): bool => is_array($section))
            ->reject(fn ($section): bool => ($section['key'] ?? null) === 'brand')
            ->map(function (array $section) use ($brandKeys): array {
                if (in_array($section['key'] ?? null, ['workspace', 'qr-codes'], true)) {
                    $section['items'] = collect((array) ($section['items'] ?? []))
                        ->filter(fn ($item): bool => is_array($item))
                        ->reject(fn ($item): bool => in_array(($item['key'] ?? null), $brandKeys, true))
                        ->values()
                        ->all();
                }

                return $section;
            })
            ->values()
            ->all();

        $qrCodesIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'qr-codes');
        $shortLinksIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'short-links');
        $contentToolsIndex = collect($sections)->search(fn ($section): bool => is_array($section) && ($section['key'] ?? null) === 'content-tools');

        if ($shortLinksIndex !== false) {
            array_splice($sections, (int) $shortLinksIndex + 1, 0, [$brandSection]);
        } elseif ($qrCodesIndex !== false) {
            array_splice($sections, (int) $qrCodesIndex + 1, 0, [$brandSection]);
        } elseif ($contentToolsIndex !== false) {
            array_splice($sections, (int) $contentToolsIndex, 0, [$brandSection]);
        } else {
            $sections[] = $brandSection;
        }

        $stored['user']['sections'] = $sections;
        $options->set('admin_menu_sidebar_overrides', $stored);
    }

    protected static function storedOverrides(OptionStore $options): array
    {
        $stored = $options->get('admin_menu_sidebar_overrides', []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($stored) ? $stored : [];
    }
}
