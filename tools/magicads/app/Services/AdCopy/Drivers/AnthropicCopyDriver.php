<?php

namespace App\Services\AdCopy\Drivers;

use App\Services\AdCopy\Contracts\AdCopyDriverInterface;
use App\Services\AdCopy\Support\EngineRegistry;
use Illuminate\Support\Facades\Http;

class AnthropicCopyDriver implements AdCopyDriverInterface
{
    /**
     * Anthropic Messages API version pin. See:
     * https://docs.anthropic.com/en/api/versioning
     */
    private const API_VERSION = '2023-06-01';

    public function generate(string $prompt, int $variants, string $apiKey, ?string $model = null): array
    {
        $model = EngineRegistry::resolveModel('anthropic', $model);

        $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => self::API_VERSION,
            ])
            ->timeout(120)
            ->acceptJson()
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => $model,
                'max_tokens' => 8192,
                'temperature' => 0.85,
                'top_p'      => 0.95,
                'system'     => 'You are a senior direct-response copywriter. Always respond with valid JSON only — no markdown, no prose, no backticks. Use the full character budget of every field; never return one short sentence when the limit allows a paragraph.',
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Anthropic API error: ' . $response->body());
        }

        // Claude returns content as an array of blocks; concatenate the text blocks.
        $blocks = $response->json('content', []);
        $text = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text .= $block['text'] ?? '';
            }
        }

        if ($text === '') {
            throw new \RuntimeException('Anthropic returned an empty response');
        }

        $parsed = $this->decodeJson($text);

        if (! is_array($parsed) || ! isset($parsed['variants']) || ! is_array($parsed['variants'])) {
            throw new \RuntimeException('Malformed Anthropic JSON response');
        }

        return array_slice($parsed['variants'], 0, $variants);
    }

    /**
     * Decode the model's JSON, tolerating an accidental ```json fence even
     * though we ask for raw JSON only.
     */
    private function decodeJson(string $text): mixed
    {
        $trimmed = trim($text);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $trimmed) ?? $trimmed;
        }

        return json_decode(trim($trimmed), true);
    }
}
