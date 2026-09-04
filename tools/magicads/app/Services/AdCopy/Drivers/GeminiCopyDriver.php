<?php

namespace App\Services\AdCopy\Drivers;

use App\Services\AdCopy\Contracts\AdCopyDriverInterface;
use App\Services\AdCopy\Support\EngineRegistry;
use Illuminate\Support\Facades\Http;

class GeminiCopyDriver implements AdCopyDriverInterface
{
    public function generate(string $prompt, int $variants, string $apiKey, ?string $model = null): array
    {
        $model = EngineRegistry::resolveModel('gemini', $model);

        $response = Http::timeout(120)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.85,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gemini API error: ' . $response->body());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! $text) {
            throw new \RuntimeException('Gemini returned an empty response');
        }

        $parsed = json_decode($text, true);

        if (! is_array($parsed) || ! isset($parsed['variants']) || ! is_array($parsed['variants'])) {
            throw new \RuntimeException('Malformed Gemini JSON response');
        }

        return array_slice($parsed['variants'], 0, $variants);
    }
}
