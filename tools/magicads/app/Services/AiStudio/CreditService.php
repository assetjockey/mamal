<?php

namespace App\Services\AiStudio;

use App\Models\MediaModel;
use App\Models\TextModel;
use App\Models\User;
use App\Services\AiStudio\Contracts\CreditServiceInterface;

class CreditService implements CreditServiceInterface
{
    /** Words per "copy" pricing unit. Copy rate is credits per 1000 words. */
    public const COPY_WORD_UNIT = 1000;

    public function hasSufficientCredits(User $user, string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): bool
    {
        return $user->hasCredits($this->getCost($type, $model, $units, $resolution));
    }

    public function deduct(User $user, string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): void
    {
        $cost = $this->getCost($type, $model, $units, $resolution);

        // Draws from `credits` first, then `credits_prepaid` (atomic, no overdraw).
        $charged = $user->spendCredits($cost);

        // Team plugin: record consumption against the user's team activity feed.
        // Guarded so core never breaks if the Team plugin is uninstalled (its
        // service class would no longer exist).
        if ($charged && class_exists(\App\Services\Team\TeamActivityRecorder::class)) {
            app(\App\Services\Team\TeamActivityRecorder::class)
                ->recordConsumption($user, $cost, ucfirst($type).' '.__('generation'), ['model' => $model, 'units' => $units]);
        }
    }

    public function getBalance(User $user): int
    {
        return $user->creditBalance();
    }

    /**
     * Total credits for a generation = per-unit rate × units.
     *
     * Rate semantics (all sourced from the model tables — no config fallback):
     *  - image : credits per image          → cost = rate × image_count
     *  - video : credits per second         → cost = rate × duration_seconds
     *  - copy  : credits per 1000 words     → cost = ceil(rate × words / 1000)
     *
     * Always charges at least 1 credit for a successful generation.
     */
    public function getCost(string $type, ?string $model = null, int|float $units = 1, ?string $resolution = null): int
    {
        $rate = $this->getRate($type, $model, $resolution);

        if ($type === 'copy') {
            $words = max(0, (int) $units);
            $cost = (int) ceil($rate * $words / self::COPY_WORD_UNIT);
        } else {
            // image: units = image count (default 1); video: units = seconds.
            $cost = (int) ceil($rate * max(1, (int) $units));
        }

        return max(1, $cost);
    }

    /**
     * The per-unit rate stored on the model row. Falls back to 1 when the
     * model is unknown so cost resolution never returns 0.
     */
    public function getRate(string $type, ?string $model = null, ?string $resolution = null): int
    {
        if (! $model) {
            return 1;
        }

        if ($type === 'copy') {
            // Copy models are keyed by model_id in text_models.
            $rate = (int) (TextModel::query()->where('model_id', $model)->value('credit_cost') ?? 0);
        } else {
            // Image/video models are keyed by vendor slug in media_models.
            $mediaModel = MediaModel::query()->where('vendor', $model)->first();

            // Per-tier pricing for engines that expose quality tiers (e.g.
            // Seedance). Falls back to the flat credit_cost when the tier is
            // absent/disabled or the model has no tiers.
            $tierRate = $resolution !== null
                ? $mediaModel?->resolutionCreditCost($resolution)
                : null;

            $rate = (int) ($tierRate ?? $mediaModel?->credit_cost ?? 0);
        }

        return $rate > 0 ? $rate : 1;
    }
}
