<?php

namespace App\Services\AiStudio\Contracts;

use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;

interface AiProviderInterface
{
    public function generateImage(GenerationRequest $request): GenerationResult;

    public function generateVideo(GenerationRequest $request): GenerationResult;

    public function supports(string $type): bool;
}
