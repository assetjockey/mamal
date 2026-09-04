<?php

namespace App\Livewire\Concerns;

use App\Models\Brand;
use App\Models\Project;

/**
 * Gives a studio component a minimal, ownership-safe awareness of the Project
 * it was launched from. A studio enters "project context" when it is opened
 * with a `?project={id}` query parameter pointing at a Project the current
 * user owns (e.g. a "New creative" link from the Project workspace).
 *
 * The resolved project id is held in a public property so it survives across
 * Livewire round-trips (the query string is only present on the initial mount),
 * which lets the generated creative stay associated with the project and lets
 * the project's Brand_Kit drive the default brand selection (Requirements
 * 10.8–10.10).
 */
trait ResolvesProjectContext
{
    /**
     * The owned Project this studio is operating within, or null when the
     * studio was opened standalone. Public so it persists across Livewire
     * requests after the initial mount.
     */
    public ?int $projectId = null;

    /**
     * Read the optional project context from the request, scope it to the
     * current user, and remember the owned project id. Returns the owned
     * Project or null (no context, or the project isn't owned by the user).
     */
    protected function resolveProjectContext(): ?Project
    {
        $raw = request('project');

        if (! filled($raw) || ! ctype_digit((string) $raw)) {
            return null;
        }

        // Ownership-safe: only ever adopt a Project the current user owns.
        $project = Project::where('user_id', auth()->id())->find((int) $raw);

        if (! $project) {
            return null;
        }

        $this->projectId = $project->id;

        return $project;
    }

    /**
     * The Brand_Kit id a new creative should default to within this project's
     * context: the project's associated Brand, but only while that Brand is
     * still owned by the current user. Returns null when there is no project
     * context or the project has no (owned) Brand_Kit, so callers apply no
     * default in that case and leave the user-provided value as-is (10.10).
     */
    protected function projectDefaultBrandId(): ?int
    {
        if (! $this->projectId) {
            return null;
        }

        $project = Project::where('user_id', auth()->id())->find($this->projectId);

        if (! $project || ! $project->brand_id) {
            return null;
        }

        // Only default to a Brand the user still owns.
        $brandId = Brand::where('user_id', auth()->id())
            ->whereKey($project->brand_id)
            ->value('id');

        return $brandId ? (int) $brandId : null;
    }
}
