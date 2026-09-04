<?php

namespace App\Concerns;

use Closure;
use Illuminate\Support\Str;

/**
 * Centralized, reusable validation rules for Projects and the project-limit
 * configuration surfaces.
 *
 * Used by the Livewire components (ProjectsIndex / ProjectWorkspace), the
 * ProjectService, and the admin configuration screens (General Settings,
 * Plan editor) so the name/description/limit rules live in exactly one place.
 *
 * The project name is trimmed before its length is validated, so leading and
 * trailing whitespace never counts toward the 1–120 character bound and a
 * whitespace-only name is rejected as empty.
 */
trait ProjectValidationRules
{
    /** Minimum length of a (trimmed) project name. */
    public static int $projectNameMin = 1;

    /** Maximum length of a (trimmed) project name. */
    public static int $projectNameMax = 120;

    /** Maximum length of a project description. */
    public static int $projectDescriptionMax = 1000;

    /** Inclusive bounds for the administrator-configured free-tier project limit. */
    public static int $freeTierLimitMin = 0;

    public static int $freeTierLimitMax = 1000;

    /** Inclusive bounds for a per-plan project limit. */
    public static int $planLimitMin = 0;

    public static int $planLimitMax = 999999;

    /**
     * Validation rules used to validate a project name.
     *
     * The name is trimmed before its length is validated: leading/trailing
     * whitespace is ignored and a whitespace-only name is treated as empty.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|\Closure|array<mixed>|string>
     */
    protected function projectNameRules(): array
    {
        return [
            'required',
            'string',
            $this->trimmedLengthRule(static::$projectNameMin, static::$projectNameMax),
        ];
    }

    /**
     * Validation rules used to validate an optional project description.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function projectDescriptionRules(): array
    {
        return ['nullable', 'string', 'max:'.static::$projectDescriptionMax];
    }

    /**
     * Validation rules used to validate the administrator-configured
     * Free_Tier_Limit (integer 0–1000 inclusive).
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function freeTierLimitRules(): array
    {
        return ['required', 'integer', 'min:'.static::$freeTierLimitMin, 'max:'.static::$freeTierLimitMax];
    }

    /**
     * Validation rules used to validate a per-plan Plan_Limit
     * (integer 0–999999 inclusive).
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function planLimitRules(): array
    {
        return ['required', 'integer', 'min:'.static::$planLimitMin, 'max:'.static::$planLimitMax];
    }

    /**
     * Normalize a project name for persistence by trimming surrounding
     * whitespace. Call this before saving so the stored value matches what was
     * length-validated.
     */
    protected function normalizeProjectName(?string $name): string
    {
        return trim((string) $name);
    }

    /**
     * Build a closure rule that validates a string's length after trimming
     * leading/trailing whitespace falls within [$min, $max] inclusive.
     *
     * @return \Closure(string, mixed, \Closure): void
     */
    protected function trimmedLengthRule(int $min, int $max): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($min, $max): void {
            $length = Str::length(trim((string) $value));

            if ($length < $min || $length > $max) {
                $fail("The :attribute must be between {$min} and {$max} characters.")->translate();
            }
        };
    }
}
