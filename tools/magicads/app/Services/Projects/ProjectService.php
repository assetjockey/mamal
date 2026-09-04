<?php

namespace App\Services\Projects;

use App\Concerns\ProjectValidationRules;
use App\Models\AdCopy;
use App\Models\AdCreative;
use App\Models\Brand;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Write-side operations for Projects: creation (with atomic limit enforcement),
 * rename and delete (with ownership scoping), and — added by later tasks —
 * creative association/detach and brand-kit association.
 *
 * Each method scopes by the owning user and validates input using the shared
 * {@see ProjectValidationRules} rules so the name/description bounds live in
 * exactly one place.
 */
final class ProjectService
{
    use ProjectValidationRules;

    public function __construct(
        private readonly ProjectLimitResolver $limitResolver = new ProjectLimitResolver,
    ) {}

    /**
     * Atomically create a Project owned by the given user, enforcing the
     * applicable Project_Limit at write time.
     *
     * The name is trimmed before validation (1–120 trimmed characters); the
     * optional description is validated to at most 1000 characters. The limit
     * check and the insert run inside a single transaction that locks the
     * owning `users` row (`lockForUpdate`), so concurrent create requests for
     * the same user are serialized and can never push the owned-project count
     * above the limit (Requirements 4.6, 9.6).
     *
     * @throws \Illuminate\Validation\ValidationException when the name/description is invalid.
     * @throws LimitReachedException when the owned-project count is at or over the limit.
     *
     * Requirements: 4.1, 4.5, 4.6, 9.4, 9.5, 9.6, 9.8
     */
    public function create(User $user, string $name, ?string $description): Project
    {
        $name = $this->normalizeProjectName($name);

        Validator::make(
            ['name' => $name, 'description' => $description],
            [
                'name' => $this->projectNameRules(),
                'description' => $this->projectDescriptionRules(),
            ],
        )->validate();

        return DB::transaction(function () use ($user, $name, $description) {
            // Lock the owning user row so the limit check and insert are atomic
            // per user; concurrent creates for the same user serialize here.
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $limit = $this->limitResolver->resolve($lockedUser);

            $ownedCount = Project::query()
                ->forUser($lockedUser->getKey())
                ->lockForUpdate()
                ->count();

            if ($ownedCount >= $limit) {
                throw LimitReachedException::forLimit($limit);
            }

            return Project::create([
                'user_id' => $lockedUser->getKey(),
                'name' => $name,
                'description' => $description,
            ]);
        });
    }

    /**
     * Rename a Project the given user owns.
     *
     * The name is trimmed before validation (1–120 trimmed characters). The
     * lookup is ownership-scoped, so renaming a Project owned by another user
     * (or a nonexistent Project) raises the framework's 404/ModelNotFound path
     * and leaves the existing name unchanged (Requirement 6.5).
     *
     * @throws \Illuminate\Validation\ValidationException when the name is invalid.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the user does not own the Project.
     *
     * Requirements: 6.1, 6.2, 6.5
     */
    public function rename(User $user, int $projectId, string $name): Project
    {
        $name = $this->normalizeProjectName($name);

        Validator::make(
            ['name' => $name],
            ['name' => $this->projectNameRules()],
        )->validate();

        $project = Project::query()
            ->forUser($user->getKey())
            ->findOrFail($projectId);

        $project->update(['name' => $name]);

        return $project;
    }

    /**
     * Delete a Project the given user owns, detaching all of its associated
     * creatives first.
     *
     * The lookup is ownership-scoped, so deleting a Project owned by another
     * user (or a nonexistent Project) raises the framework's 404/ModelNotFound
     * path and leaves all state unchanged (Requirement 6.5). The detach + delete
     * run inside a single transaction: every associated Ad_Copy and Ad_Creative
     * has its `project_id` nulled (retained as Unassigned records) and then the
     * Project is removed. If any step fails the transaction rolls back, so the
     * Project and its creatives' references are retained (Requirements 6.7, 6.8).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the user does not own the Project.
     *
     * Requirements: 6.3, 6.5, 6.7, 6.8
     */
    public function delete(User $user, int $projectId): void
    {
        $project = Project::query()
            ->forUser($user->getKey())
            ->findOrFail($projectId);

        DB::transaction(function () use ($project) {
            $project->adCopies()->update(['project_id' => null]);
            $project->adCreatives()->update(['project_id' => null]);

            $project->delete();
        });
    }

    /**
     * Creative `$type` values accepted by {@see associateCreative()} and
     * {@see detachCreative()}.
     *
     * The convention is the surface the creative came from:
     *   - `'copy'`               → an Ad_Copy record   ({@see AdCopy})
     *   - `'image'` | `'video'`  → an Ad_Creative record ({@see AdCreative}),
     *                              matching the creative's own `type` column
     *   - `'creative'`           → an Ad_Creative record (type-agnostic alias,
     *                              useful when the caller only knows it is an
     *                              image/video creative)
     *
     * `'image'` and `'video'` both resolve to {@see AdCreative}; they are
     * accepted so callers (e.g. the ProjectWorkspace sections) can pass the
     * creative's own type verbatim without first mapping it.
     *
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private const CREATIVE_TYPES = [
        'copy' => AdCopy::class,
        'image' => AdCreative::class,
        'video' => AdCreative::class,
        'creative' => AdCreative::class,
    ];

    /**
     * Associate a Creative the given user owns with a Project the given user owns.
     *
     * Both the Project and the Creative lookups are ownership-scoped: a Project
     * or Creative owned by another user (or a nonexistent id) raises the
     * framework's 404/ModelNotFound path and leaves all state unchanged
     * (Requirements 5.5, 5.6) — consistent with {@see rename()} / {@see delete()}.
     *
     * `$type` selects the creative surface (see {@see CREATIVE_TYPES}): `'copy'`
     * targets an Ad_Copy, while `'image'`, `'video'`, and `'creative'` target an
     * Ad_Creative. Setting the Creative's `project_id` to the target Project also
     * covers reassignment: a Creative already pointing at another Project simply
     * has its single reference replaced, which leaves every other Project's
     * associated Creatives untouched (Requirements 5.1, 5.2, 5.3).
     *
     * @throws InvalidArgumentException when `$type` is not a supported creative type.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the user does not own the Project or the Creative.
     *
     * Requirements: 5.1, 5.2, 5.3, 5.5, 5.6
     */
    public function associateCreative(User $user, int $projectId, string $type, int $creativeId): void
    {
        $project = Project::query()
            ->forUser($user->getKey())
            ->findOrFail($projectId);

        $creative = $this->ownedCreativeQuery($type, $user)
            ->findOrFail($creativeId);

        $creative->update(['project_id' => $project->getKey()]);
    }

    /**
     * Detach a Creative the given user owns from whatever Project it references,
     * retaining it as an Unassigned record owned by the same user.
     *
     * The Creative lookup is ownership-scoped, so detaching a Creative owned by
     * another user (or a nonexistent id) raises the framework's 404/ModelNotFound
     * path and leaves all state unchanged (Requirement 5.6).
     *
     * @throws InvalidArgumentException when `$type` is not a supported creative type.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the user does not own the Creative.
     *
     * Requirements: 5.4, 5.6
     */
    public function detachCreative(User $user, string $type, int $creativeId): void
    {
        $creative = $this->ownedCreativeQuery($type, $user)
            ->findOrFail($creativeId);

        $creative->update(['project_id' => null]);
    }

    /**
     * Set, change, or remove the Brand_Kit (the {@see Brand} model) associated
     * with a Project the given user owns.
     *
     * The Project lookup is ownership-scoped, so operating on a Project owned by
     * another user (or a nonexistent id) raises the framework's 404/ModelNotFound
     * path and leaves the Project's `brand_id` unchanged (Requirement 10.6). When
     * `$brandId` is non-null, the Brand is also looked up ownership-scoped: a
     * Brand_Kit owned by another user (or a nonexistent id) raises ModelNotFound
     * and the Project's existing `brand_id` is left untouched (Requirement 10.5).
     * Passing `null` removes the association (Requirement 10.4).
     *
     * The change runs inside a transaction; if persistence fails the transaction
     * rolls back and the Project retains its previously persisted `brand_id`
     * (Requirement 10.7).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException when the user does not own the Project or the Brand_Kit.
     *
     * Requirements: 10.3, 10.4, 10.5, 10.6, 10.7
     */
    public function setBrandKit(User $user, int $projectId, ?int $brandId): Project
    {
        $project = Project::query()
            ->forUser($user->getKey())
            ->findOrFail($projectId);

        if ($brandId !== null) {
            // Ownership-scoped lookup; a Brand the user does not own raises
            // ModelNotFound and the Project's brand_id is left unchanged.
            Brand::query()
                ->where('user_id', $user->getKey())
                ->findOrFail($brandId);
        }

        return DB::transaction(function () use ($project, $brandId) {
            $project->update(['brand_id' => $brandId]);

            return $project;
        });
    }

    /**
     * Build an ownership-scoped query for the creative surface named by `$type`.
     *
     * Restricts the query to records owned by `$user` so the subsequent
     * `findOrFail` enforces creative ownership (Requirements 5.5, 5.6).
     *
     * @throws InvalidArgumentException when `$type` is not a supported creative type.
     */
    private function ownedCreativeQuery(string $type, User $user): Builder
    {
        $model = self::CREATIVE_TYPES[strtolower(trim($type))] ?? null;

        if ($model === null) {
            throw new InvalidArgumentException(
                "Unsupported creative type [{$type}]. Expected one of: "
                .implode(', ', array_keys(self::CREATIVE_TYPES)).'.'
            );
        }

        return $model::query()->where('user_id', $user->getKey());
    }
}
