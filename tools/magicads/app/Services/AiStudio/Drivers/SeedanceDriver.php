<?php

namespace App\Services\AiStudio\Drivers;

use App\Models\AdminKey;
use App\Models\MediaModel;
use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\Drivers\Seedance\SeedanceBytedanceClient;
use App\Services\AiStudio\Drivers\Seedance\SeedanceFalClient;
use App\Services\AiStudio\Drivers\Seedance\SeedanceKieClient;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;

/**
 * Seedance 2.0 — ByteDance's second-generation video model.
 *
 * Seedance can be powered by any one of three interchangeable API vendors,
 * each with a completely different request/response shape:
 *
 *   - bytedance : ByteDance's own API (Volcengine Ark / BytePlus) — the
 *                 original first-party vendor. Key: admin_keys.seedance_key.
 *   - fal       : fal.ai's hosted queue route. Key: admin_keys.fal_key.
 *   - kie       : kie.ai's unified Market jobs API. Key: admin_keys.kie_key.
 *
 * Exactly ONE vendor is active at a time, chosen by the admin on the AI
 * Settings page (media_models.api_provider) and it must have an API key.
 *
 * This driver is a thin dispatcher: it selects the vendor client and delegates
 * the actual submit/poll work. To stay correct even if an admin switches the
 * active vendor while a clip is mid-render, the vendor used at submit time is
 * encoded into the operation id (`vendor::opId`) and re-read on every poll —
 * so a fal.ai job is always polled against fal.ai regardless of the current
 * selection. Keys are resolved per-vendor here, so the constructor key is only
 * a compatibility placeholder.
 */
class SeedanceDriver implements AiProviderInterface, AsyncVideoProviderInterface
{
    use DownloadsVideoResult;

    /** Separator between the vendor tag and the vendor's own operation id. */
    protected const OP_SEPARATOR = '::';

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Seedance does not support image generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'video';
    }

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        $vendor = $this->activeVendor($request->provider);
        $client = $this->clientFor($vendor, $request->provider);

        if (! $client) {
            return GenerationResult::failure('Seedance has no active vendor configured. Set one in AI Settings.');
        }

        $result = $client->submitVideo($request);

        // Only rewrite the operation id on a clean submit; pass rate-limit /
        // failure results straight through untouched.
        if (! $result->success || ! $result->operationId) {
            return $result;
        }

        return GenerationResult::submitted($vendor.self::OP_SEPARATOR.$result->operationId);
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        [$vendor, $vendorOpId] = $this->parseOperationId($operationId, $request->provider);
        $client = $this->clientFor($vendor, $request->provider);

        if (! $client) {
            return GenerationResult::failure('Seedance vendor for this job is no longer configured.');
        }

        return $client->pollVideo($vendorOpId, $request);
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        return $this->blockingGenerate($request, 'Seedance', pollInterval: 6, timeout: 300);
    }

    // ------------------------------------------------------------------
    // Vendor resolution
    // ------------------------------------------------------------------

    /**
     * The vendor key currently selected to power Seedance, defaulting to
     * fal.ai when the model row carries no multi-vendor config (legacy rows).
     */
    protected function activeVendor(string $providerSlug): string
    {
        return MediaModel::findByVendor($providerSlug)?->activeProvider() ?? 'fal';
    }

    /**
     * Build the vendor client, resolving that vendor's own API key + concrete
     * model id from the model row. Returns null when no key is configured.
     */
    protected function clientFor(string $vendor, string $providerSlug): ?AsyncVideoProviderInterface
    {
        $model = MediaModel::findByVendor($providerSlug);
        $config = $model?->providerConfig($vendor);

        $keyField = $config['key_field'] ?? null;
        $modelId = $config['model_id'] ?? null;

        $apiKey = $keyField ? (AdminKey::first()?->apiKey($keyField) ?? '') : '';

        if ($apiKey === '') {
            return null;
        }

        return match ($vendor) {
            'bytedance' => new SeedanceBytedanceClient($apiKey, $modelId),
            'kie'       => new SeedanceKieClient($apiKey, $modelId),
            default     => new SeedanceFalClient($apiKey, $modelId),
        };
    }

    /**
     * Split a stored operation id back into [vendor, vendorOperationId].
     * Falls back to the model's current active vendor for legacy (untagged)
     * ids written before multi-vendor support.
     *
     * @return array{0:string, 1:string}
     */
    protected function parseOperationId(string $operationId, string $providerSlug): array
    {
        if (str_contains($operationId, self::OP_SEPARATOR)) {
            [$vendor, $vendorOpId] = explode(self::OP_SEPARATOR, $operationId, 2);

            return [$vendor, $vendorOpId];
        }

        return [$this->activeVendor($providerSlug), $operationId];
    }
}
