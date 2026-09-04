<?php

namespace App\Livewire\User\Projects;

use App\Models\AdCopy;
use App\Models\AdCreative;
use App\Models\Brand;
use App\Models\Project;
use App\Services\Projects\ProjectService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Throwable;

/**
 * Single-Project workspace. Loads an owner-scoped Project in {@see mount()}
 * (an unowned or nonexistent id raises the framework's 404 → "Project
 * unavailable" path, Requirements 1.10, 3.5) and renders its name/description,
 * three creative-type sections (Ad Copy, Images, Videos — each with a per-group
 * empty state), the associated Brand_Kit panel (or an empty state), an
 * "associate" control listing the user's Unassigned creatives, detach controls
 * on associated creatives, a rename action, and a confirm-gated delete action.
 *
 * Every mutation delegates to {@see ProjectService}, which is ownership-scoped
 * and atomic; this component surfaces Toaster confirmations on success and
 * Toaster errors on failure (catching {@see ModelNotFoundException} and any
 * other {@see Throwable}). A failed delete keeps the user on the workspace with
 * all state intact (Requirement 6.8).
 *
 * Requirements: 1.10, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 5.1, 5.2, 5.4, 5.7,
 * 6.3, 6.4, 6.6, 6.8, 10.3, 10.4, 10.7
 */
#[Title('Project')]
class ProjectWorkspace extends Component
{
    /** The owning id of the Project being viewed (validated in mount()). */
    public int $projectId;

    /** Working copy of the name, bound to the rename modal input. */
    public string $editName = '';

    /**
     * Confirmation gate for deletion. Deletion is only performed once the user
     * has taken an explicit confirmation action that flips this to true, which
     * satisfies Requirement 6.4 (no deletion until confirmation is received).
     */
    public bool $confirmingDelete = false;

    /**
     * Load the Project ownership-scoped. A Project owned by another user or a
     * nonexistent id raises ModelNotFoundException, which the framework renders
     * as a 404 "Project unavailable" page (Requirements 1.10, 3.5).
     */
    public function mount(int $id): void
    {
        // Owner-scoped, widened to include projects shared with the user via
        // the Team plugin (read access). Owner-only mutations remain enforced
        // by the ownership-scoped ProjectService.
        $project = Project::query()
            ->accessibleBy(auth()->id())
            ->findOrFail($id);

        $this->projectId = $project->getKey();
        $this->editName = $project->name;
    }

    /**
     * Rename the Project. Validation/ownership live in the service; a validation
     * failure maps to a field error plus a Toaster error (Requirement 6.2),
     * success shows a confirmation Toaster (Requirement 6.6).
     *
     * Requirements: 6.6
     */
    public function rename(ProjectService $projects): void
    {
        try {
            $project = $projects->rename(auth()->user(), $this->projectId, $this->editName);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            Toaster::error($e->validator->errors()->first() ?: __('Please enter a valid project name.'));

            return;
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('This project is unavailable.'));

            return;
        } catch (Throwable $e) {
            Toaster::error(__('The project could not be renamed.'));

            return;
        }

        $this->resetValidation();
        $this->dispatch('modal-close', name: 'rename-project');

        Toaster::success(__('Project renamed to ":name".', ['name' => $project->name]));
    }

    /**
     * Explicit confirmation action that arms the delete (Requirement 6.4).
     */
    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    /**
     * Delete the Project once confirmation has been received. The service
     * detaches the Project's creatives and removes it inside a transaction; on
     * failure the state is retained and the user stays on the workspace with a
     * Toaster error (Requirements 6.3, 6.8).
     */
    public function delete(ProjectService $projects)
    {
        if (! $this->confirmingDelete) {
            return null;
        }

        try {
            $projects->delete(auth()->user(), $this->projectId);
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('This project is unavailable.'));

            return null;
        } catch (Throwable $e) {
            // Deletion failed after confirmation: the service rolled back, so
            // the Project and its creative references are retained (6.8).
            $this->confirmingDelete = false;
            Toaster::error(__('The project could not be deleted. Please try again.'));

            return null;
        }

        Toaster::success(__('Project deleted.'));

        return $this->redirectRoute('user.projects.index', navigate: true);
    }

    /**
     * Associate an Unassigned Creative the user owns with this Project
     * (Requirements 5.1, 5.2, 5.7). `$type` is the creative surface: `copy`
     * for an Ad_Copy, or the Ad_Creative's own `image`/`video` type.
     */
    public function associate(string $type, int $creativeId, ProjectService $projects): void
    {
        try {
            $projects->associateCreative(auth()->user(), $this->projectId, $type, $creativeId);
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('That item is unavailable.'));

            return;
        } catch (Throwable $e) {
            Toaster::error(__('The item could not be added to this project.'));

            return;
        }

        Toaster::success(__('Added to project.'));
    }

    /**
     * Detach a Creative from this Project, retaining it as an Unassigned record
     * owned by the same user (Requirement 5.4).
     */
    public function detach(string $type, int $creativeId, ProjectService $projects): void
    {
        try {
            $projects->detachCreative(auth()->user(), $type, $creativeId);
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('That item is unavailable.'));

            return;
        } catch (Throwable $e) {
            Toaster::error(__('The item could not be removed from this project.'));

            return;
        }

        Toaster::success(__('Removed from project.'));
    }

    /**
     * Set or change this Project's associated Brand_Kit (Requirement 10.3). A
     * Brand owned by another user raises ModelNotFound; a persistence failure
     * retains the previous value (Requirement 10.7).
     */
    public function setBrandKit(int $brandId, ProjectService $projects): void
    {
        try {
            $projects->setBrandKit(auth()->user(), $this->projectId, $brandId);
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('That brand kit is unavailable.'));

            return;
        } catch (Throwable $e) {
            Toaster::error(__('The brand kit could not be updated.'));

            return;
        }

        $this->dispatch('modal-close', name: 'project-brand-kit');

        Toaster::success(__('Brand kit updated.'));
    }

    /**
     * Remove this Project's associated Brand_Kit (Requirement 10.4). A failure
     * retains the previously persisted value (Requirement 10.7).
     */
    public function removeBrandKit(ProjectService $projects): void
    {
        try {
            $projects->setBrandKit(auth()->user(), $this->projectId, null);
        } catch (ModelNotFoundException $e) {
            Toaster::error(__('This project is unavailable.'));

            return;
        } catch (Throwable $e) {
            Toaster::error(__('The brand kit could not be removed.'));

            return;
        }

        $this->dispatch('modal-close', name: 'project-brand-kit');

        Toaster::success(__('Brand kit removed.'));
    }

    public function render()
    {
        $userId = auth()->id();

        // Re-load (owner or shared access) so a Project deleted/un-shared from
        // under the user still resolves to the framework 404 path.
        $project = Project::query()
            ->accessibleBy($userId)
            ->findOrFail($this->projectId);

        $adCopies = $project->adCopies()->latest()->get();
        $images = $project->adCreatives()->where('type', 'image')->latest()->get();
        $videos = $project->adCreatives()->where('type', 'video')->latest()->get();

        // Unassigned creatives owned by the user, offered by the associate
        // control (Requirement 5.7).
        $unassignedCopies = AdCopy::query()
            ->where('user_id', $userId)
            ->whereNull('project_id')
            ->latest()
            ->get();

        $unassignedCreatives = AdCreative::query()
            ->where('user_id', $userId)
            ->whereNull('project_id')
            ->latest()
            ->get();

        // The user's owned Brands offered by the brand-kit control.
        $brands = Brand::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('livewire.user.projects.project-workspace', [
            'project' => $project,
            'adCopies' => $adCopies,
            'images' => $images,
            'videos' => $videos,
            'unassignedCopies' => $unassignedCopies,
            'unassignedCreatives' => $unassignedCreatives,
            'hasUnassigned' => $unassignedCopies->isNotEmpty() || $unassignedCreatives->isNotEmpty(),
            'brand' => $project->brand,
            'brands' => $brands,
        ]);
    }
}
