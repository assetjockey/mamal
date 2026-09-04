<?php

namespace App\Services\AdCopy\Drivers;

use App\Services\AdCopy\Contracts\AdCopyDriverInterface;
use App\Services\AdCopy\Support\EngineRegistry;
use Illuminate\Support\Facades\Http;

class GrokCopyDriver implements AdCopyDriverInterface
{
    public function generate(string $prompt, int $variants, string $apiKey, ?string $model = null): array
    {
        $model = EngineRegistry::resolveModel('xai', $model);

        // xAI exposes an OpenAI-compatible chat completions endpoint.
        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->acceptJson()
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a senior direct-response copywriter. Always respond with valid JSON only — no markdown, no prose, no backticks. Use the full character budget of every field; never return one short sentence when the limit allows a paragraph.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.85,
                'top_p' => 0.95,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('xAI Grok API error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        if (! $content) {
            throw new \RuntimeException('xAI Grok returned an empty response');
        }

        $parsed = json_decode($content, true);

        if (! is_array($parsed) || ! isset($parsed['variants']) || ! is_array($parsed['variants'])) {
            throw new \RuntimeException('Malformed xAI Grok JSON response');
        }

        return array_slice($parsed['variants'], 0, $variants);
    }
}
