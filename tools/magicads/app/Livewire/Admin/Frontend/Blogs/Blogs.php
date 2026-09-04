<?php

namespace App\Livewire\Admin\Frontend\Blogs;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Blogs Manager')]
class Blogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle a post's featured flag straight from the list.
     */
    public function toggleFeatured(int $id): void
    {
        $post = BlogPost::find($id);

        if ($post) {
            $post->update(['is_featured' => ! $post->is_featured]);
            toaster()->success(__('Featured status updated'));
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $post = BlogPost::select('id', 'featured_image')->find($this->deleteId);

        if ($post) {
            // Remove the stored cover image when it's a locally uploaded file
            // (skip http URLs and inline data-URIs used by the seeder).
            if (
                filled($post->featured_image)
                && ! str_starts_with((string) $post->featured_image, 'http')
                && ! str_starts_with((string) $post->featured_image, 'data:')
                && Storage::disk('public')->exists($post->featured_image)
            ) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Cascades to comments via the FK constraint.
            $post->delete();
            toaster()->success(__('Blog post deleted successfully'));
        }

        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $posts = BlogPost::query()
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->withCount([
                'allComments as comments_count',
                'allComments as pending_comments_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.frontend.blogs.index', [
            'posts' => $posts,
        ]);
    }
}
