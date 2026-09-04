<?php

namespace Modules\AppShortLinkApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkApi\Models\AppShortLinkApiKey;
use Modules\AppShortLinkApi\Support\ShortLinkWebhookDispatcher;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkApiController
{
    public function index(Request $request): JsonResponse
    {
        $apiKey = $this->apiKey($request);

        if (! $apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! app(ShortLinkPlanLimits::class)->canUseFeatureForOwner((int) $apiKey->owner_user_id, 'short_link_api_access')) {
            return response()->json(['message' => 'Short Links API access is not included in this plan.'], 403);
        }

        $links = AppShortLink::query()
            ->where('owner_user_id', $apiKey->owner_user_id)
            ->latest()
            ->limit((int) min(100, max(1, $request->integer('limit', 25))))
            ->get()
            ->map(fn (AppShortLink $link): array => $this->serialize($link))
            ->values();

        return response()->json(['data' => $links]);
    }

    public function store(Request $request): JsonResponse
    {
        $apiKey = $this->apiKey($request);

        if (! $apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limits = app(ShortLinkPlanLimits::class);

        if (! $limits->canUseFeatureForOwner((int) $apiKey->owner_user_id, 'short_link_api_access')) {
            return response()->json(['message' => 'Short Links API access is not included in this plan.'], 403);
        }

        if (! $limits->canCreateLinkForOwner((int) $apiKey->owner_user_id)) {
            return response()->json(['message' => 'Short link limit reached for this plan.'], 403);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:160'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_code' => ['nullable', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
        ]);

        $name = trim((string) ($validated['name'] ?? ''));
        $host = parse_url((string) $validated['destination_url'], PHP_URL_HOST);
        $name = $name !== '' ? $name : ($host ? 'Link '.$host : 'Untitled link');
        $code = trim((string) ($validated['custom_code'] ?? ''));

        $link = AppShortLink::query()->create([
            'owner_user_id' => $apiKey->owner_user_id,
            'team_id' => $apiKey->team_id,
            'name' => $name,
            'destination_url' => $validated['destination_url'],
            'short_code' => $code !== '' ? str($code)->lower()->value() : AppShortLink::uniqueShortCode($name),
            'status' => 'active',
            'password_hash' => filled($validated['password'] ?? null) ? Hash::make((string) $validated['password']) : null,
        ]);

        app(ShortLinkWebhookDispatcher::class)->dispatch((int) $apiKey->owner_user_id, 'short_link.created', $link);

        return response()->json(['data' => $this->serialize($link)], 201);
    }

    protected function apiKey(Request $request): ?AppShortLinkApiKey
    {
        $token = trim((string) $request->bearerToken());

        if ($token === '') {
            return null;
        }

        $apiKey = AppShortLinkApiKey::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('is_active', true)
            ->first();

        $apiKey?->forceFill(['last_used_at' => now()])->save();

        return $apiKey;
    }

    protected function serialize(AppShortLink $link): array
    {
        return [
            'id' => $link->id,
            'name' => $link->name,
            'short_code' => $link->short_code,
            'short_url' => $link->shortUrl(),
            'destination_url' => $link->destination_url,
            'status' => $link->status,
            'clicks_count' => (int) $link->clicks_count,
            'created_at' => $link->created_at?->toISOString(),
        ];
    }
}
