<?php

namespace App\Services\AiStudio\Drivers;

use App\Models\AdminKey;
use App\Models\MediaModel;
use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\Drivers\Kling\KlingFalClient;
use App\Services\AiStudio\Drivers\Kling\KlingKieClient;
use App\Services\AiStudio\Drivers\Kling\KlingKlingaiClient;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;

/**
 * Kling 3.0 — Kuaishou's cinematic video model.
 *
 * Kling can be powered by any one of three interchangeable API vendors, each
 * with a completely different request/response shape:
 *
 *   - klingai : Kling AI's own API (Kuaishou, https://kling.ai) — the original
 *               first-party vendor. Key: admin_keys.kling_key ("ak:sk" JWT).
 *   - fal     : fal.ai's hosted queue route. Key: admin_keys.fal_key.
 *   - kie     : kie.ai's unified Market jobs API. Key: admin_keys.kie_key.
 *
 * Exactly ONE vendor is active at a time, chosen by the admin on the AI
 * Settings page (media_models.api_provider) and it must have an API key.
 *
 * Like SeedanceDriver, this is a thin dispatcher: it selects the vendor client
 * and delegates submit/poll. The vendor used at submit time is encoded into the
 * operation id (`vendor::opId`) and re-read on every poll, so a job is always
 * polled against the vendor that started it even if the admin switches the
 * active vendor mid-render. Keys are resolved per-vendor here, so the
 * constructor key is only a compatibility placeholder.
 */
class KlingDriver implements AiProviderInterface, AsyncVideoProviderInterface
{
    use DownloadsVideoResult;

    /** Separator between the vendor tag and the vendor's own operation id. */
    protected const OP_SEPARATOR = '::';

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Kling does not support image generation.');
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
            return GenerationResult::failure('Kling has no active vendor configured. Set one in AI Settings.');
        }

        $result = $client->submitVideo($request);

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
            return GenerationResult::failure('Kling vendor for this job is no longer configured.');
        }

        return $client->pollVideo($vendorOpId, $request);
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        return $this->blockingGenerate($request, 'Kling', pollInterval: 8, timeout: 300);
    }

    // ------------------------------------------------------------------
    // Vendor resolution
    // ------------------------------------------------------------------

    protected function activeVendor(string $providerSlug): string
    {
        return MediaModel::findByVendor($providerSlug)?->activeProvider() ?? 'fal';
    }

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
            'klingai' => new KlingKlingaiClient($apiKey, $modelId),
            'kie'     => new KlingKieClient($apiKey, $modelId),
            default   => new KlingFalClient($apiKey, $modelId),
        };
    }

    /**
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
