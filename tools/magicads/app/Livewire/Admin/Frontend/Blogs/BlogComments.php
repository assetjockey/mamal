<?php

namespace App\Livewire\Admin\Frontend\Blogs;

use App\Models\BlogComment;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Blog Comments')]
class BlogComments extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'pending';

    public ?int $deleteId = null;
    public bool $showDeleteModal = false;

    /** Valid moderation states (mirrors the blog_comments enum). */
    protected array $statuses = ['pending', 'approved', 'spam', 'rejected'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $id, string $status): void
    {
        if (! in_array($status, $this->statuses, true)) {
            return;
        }

        $comment = BlogComment::find($id);

        if ($comment) {
            $comment->update(['status' => $status]);
            toaster()->success(__('Comment marked as :status', ['status' => __(ucfirst($status))]));
        }
    }

    public function approve(int $id): void
    {
        $this->setStatus($id, 'approved');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            BlogComment::whereKey($this->deleteId)->delete();
            toaster()->success(__('Comment deleted successfully'));
        }

        $this->deleteId = null;
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $comments = BlogComment::query()
            ->with('post:id,title,slug')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('content', 'like', $like);
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        $counts = [
            'pending' => BlogComment::where('status', 'pending')->count(),
            'approved' => BlogComment::where('status', 'approved')->count(),
            'spam' => BlogComment::where('status', 'spam')->count(),
            'rejected' => BlogComment::where('status', 'rejected')->count(),
        ];

        return view('livewire.admin.frontend.blogs.comments', [
            'comments' => $comments,
            'counts' => $counts,
        ]);
    }
}
