<?php

namespace App\Services\AdCopy\Contracts;

interface AdCopyDriverInterface
{
    /**
     * Generate N ad copy variants.
     *
     * @param  string       $prompt    Fully-assembled system + user prompt
     * @param  int          $variants  Number of variants to produce
     * @param  string       $apiKey    Provider API key
     * @param  string|null  $model     Optional model id (e.g. 'gpt-4o', 'gemini-2.5-pro').
     *                                 When null the driver falls back to a sensible default.
     * @return array<int, array<string, string>>  Array of N variants, each an associative array keyed by field slug
     */
    public function generate(string $prompt, int $variants, string $apiKey, ?string $model = null): array;
}
