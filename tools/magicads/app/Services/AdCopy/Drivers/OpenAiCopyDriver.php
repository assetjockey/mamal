<?php

namespace App\Services\AdCopy\Drivers;

use App\Services\AdCopy\Contracts\AdCopyDriverInterface;
use App\Services\AdCopy\Support\EngineRegistry;
use Illuminate\Support\Facades\Http;

class OpenAiCopyDriver implements AdCopyDriverInterface
{
    public function generate(string $prompt, int $variants, string $apiKey, ?string $model = null): array
    {
        $model = EngineRegistry::resolveModel('openai', $model);

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a senior direct-response copywriter. Always respond with valid JSON only — no markdown, no prose, no backticks. Use the full character budget of every field; never return one short sentence when the limit allows a paragraph.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        // GPT-5 (and o-series) models only accept the default temperature, reject
        // the presence/frequency penalty + top_p tuning, and require
        // `max_completion_tokens` instead of the legacy `max_tokens`. Older GPT-4
        // class models keep the richer sampling controls.
        if ($this->isReasoningModel($model)) {
            $payload['max_completion_tokens'] = 8192;
        } else {
            $payload['temperature'] = 0.85;
            $payload['top_p'] = 0.95;
            $payload['presence_penalty'] = 0.4;
            $payload['frequency_penalty'] = 0.3;
            $payload['max_tokens'] = 4096;
        }

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $content = $response->json('choices.0.message.content');

        if (! $content) {
            throw new \RuntimeException('OpenAI returned an empty response');
        }

        $parsed = json_decode($content, true);

        if (! is_array($parsed) || ! isset($parsed['variants']) || ! is_array($parsed['variants'])) {
            throw new \RuntimeException('Malformed OpenAI JSON response');
        }

        return array_slice($parsed['variants'], 0, $variants);
    }

    /**
     * GPT-5 and o-series models use the reasoning-style chat parameters:
     * default temperature only, no penalty/top_p tuning, and
     * `max_completion_tokens` rather than `max_tokens`.
     */
    private function isReasoningModel(string $model): bool
    {
        return (bool) preg_match('/^(gpt-5|o\d)/i', $model);
    }
}
