<?php

namespace App\Services\Projects;

use RuntimeException;

/**
 * Thrown when a user attempts to create a Project while their owned-project
 * count has reached the applicable Project_Limit.
 *
 * The UI maps this single failure type to the "limit reached + upgrade"
 * message (Requirements 4.5, 9.5).
 */
class LimitReachedException extends RuntimeException
{
    /**
     * Create an exception describing the limit that was reached.
     */
    public static function forLimit(int $limit): self
    {
        return new self("The project limit of {$limit} has been reached.");
    }
}
