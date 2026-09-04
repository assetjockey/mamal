<?php

namespace App\Services\AiStudio\Contracts;

use App\Models\User;

interface CreditServiceInterface
{
    public function hasSufficientCredits(User $user, string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): bool;

    public function deduct(User $user, string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): void;

    public function getBalance(User $user): int;

    /**
     * Total credits for a generation = per-unit rate × units.
     *  - image : rate is "credits per image",        units = image count
     *  - video : rate is "credits per second",       units = clip duration (s)
     *  - copy  : rate is "credits per 1000 words",   units = words generated
     *
     * $resolution optionally selects a per-tier rate for video engines that
     * price per quality tier (e.g. Seedance 480p/720p/1080p/4k).
     */
    public function getCost(string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): int;

    /**
     * The raw per-unit rate stored on the model row (no unit multiplication).
     * Used by the UI to show "X credits / image", "X / sec", "X / 1k words".
     *
     * $resolution optionally selects the per-tier rate for video engines that
     * price per quality tier.
     */
    public function getRate(string $type, ?string $model = null, ?string $resolution = null): int;
}
