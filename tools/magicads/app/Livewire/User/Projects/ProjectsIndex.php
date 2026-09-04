<?php

namespace App\Livewire\User\Projects;

use App\Models\Project;
use App\Services\Projects\LimitReachedException;
use App\Services\Projects\ProjectLimitResolver;
use App\Services\Projects\ProjectService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

/**
 * Lists the owner-scoped Projects for the signed-in user, ordered by most
 * recently updated, with a per-project creative count, an empty state that
 * starts creation of the first Project, and a create modal that delegates to
 * {@see ProjectService::create()}.
 *
 * Limit handling: the create control is rendered disabled with an explanatory
 * message (and an upgrade link) whenever the user's owned-project count is at
 * or over the applicable Project_Limit (Requirements 9.5, 9.7). The service
 * still enforces the limit atomically at write time, so a {@see LimitReachedException}
 * raised there is surfaced as a Toaster error pointing the user at the upgrade
 * page.
 *
 * Requirements: 3.1, 3.2, 3.3, 3.10, 4.2, 4.3, 4.5, 9.5, 9.7
 */
#[Title('Projects')]
class ProjectsIndex extends Component
{
    use WithPagination;

    /** Name entered in the create modal. */
    public string $name = '';

    /** Optional description entered in the create modal. */
    public string $description = '';

    /**
     * Create a Project owned by the signed-in user.
     *
     * Validation, the limit check, and the insert all happen inside the service
     * (atomically). A validation failure maps to field errors plus a Toaster
     * error identifying the name field (Requirement 4.4); a limit failure maps
     * to a Toaster error with an upgrade link (Requirements 4.5, 9.5); success
     * shows a confirmation Toaster and refreshes the list (Requirements 4.2, 4.3).
     */
    public function create(ProjectService $projects): void
    {
        try {
            $project = $projects->create(auth()->user(), $this->name, $this->description);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            Toaster::error($e->validator->errors()->first() ?: __('Please enter a valid project name.'));

            return;
        } catch (LimitReachedException $e) {
            Toaster::error(__('You have reached your project limit. Upgrade your plan to create more projects.'));

            return;
        }

        $this->reset('name', 'description');
        $this->resetValidation();
        $this->resetPage();

        $this->dispatch('modal-close', name: 'create-project');

        Toaster::success(__('Project ":name" created.', ['name' => $project->name]));
    }

    public function render(ProjectLimitResolver $limitResolver)
    {
        $user = auth()->user();

        $projects = Project::query()
            ->forUser($user->id)
            ->orderByDesc('updated_at')
            ->paginate(12);

        $ownedCount = Project::query()->forUser($user->id)->count();
        $limit = $limitResolver->resolve($user);

        return view('livewire.user.projects.projects-index', [
            'projects' => $projects,
            'ownedCount' => $ownedCount,
            'projectLimit' => $limit,
            'atLimit' => $ownedCount >= $limit,
            'upgradeUrl' => \Illuminate\Support\Facades\Route::has('user.billing')
                ? route('user.billing')
                : null,
        ]);
    }
}
