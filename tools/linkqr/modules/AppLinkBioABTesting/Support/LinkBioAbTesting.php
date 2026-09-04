<?php

namespace Modules\AppLinkBioABTesting\Support;

use Illuminate\Http\Request;

class LinkBioAbTesting
{
    public function variantFor(Request $request, array $item): array
    {
        $variants = collect((array) data_get($item, 'ab_variants', []))
            ->filter(fn ($variant): bool => is_array($variant) && (bool) data_get($variant, 'enabled', true))
            ->values();

        if ($variants->isEmpty()) {
            return ['key' => null, 'item' => $item];
        }

        $bucket = $this->bucket($request);
        $cursor = 0;

        foreach ($variants as $index => $variant) {
            $weight = max(1, min(100, (int) data_get($variant, 'weight', 50)));
            $cursor += $weight;

            if ($bucket < $cursor) {
                return [
                    'key' => (string) data_get($variant, 'key', chr(65 + $index)),
                    'item' => array_merge($item, array_filter([
                        'label' => data_get($variant, 'label'),
                        'url' => data_get($variant, 'url'),
                        'note' => data_get($variant, 'note'),
                    ], fn ($value): bool => filled($value))),
                ];
            }
        }

        return ['key' => null, 'item' => $item];
    }

    protected function bucket(Request $request): int
    {
        $seed = implode('|', [
            (string) $request->ip(),
            (string) $request->userAgent(),
            (string) $request->headers->get('accept-language', ''),
        ]);

        return hexdec(substr(hash('sha256', $seed), 0, 8)) % 100;
    }
}
