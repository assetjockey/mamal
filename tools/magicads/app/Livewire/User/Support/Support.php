<?php

namespace App\Livewire\User\Support;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Title('Support')]
class Support extends Component
{
    use WithFileUploads;
    use WithPagination;

    /** Allowed values mirror the admin filters + migration comment. */
    public const CATEGORIES = ['technical', 'billing', 'account', 'general', 'request'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    /** Toggles the inline "new ticket" panel. Driven by the route on mount. */
    public bool $creating = false;

    // --- Create form ---
    public string $subject = '';
    public string $category = 'general';
    public string $priority = 'medium';
    public string $message = '';
    public $attachment;

    // --- List filters (query-string bound so the view is shareable) ---
    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        // /support/create lands on the same component — open straight into the form.
        $this->creating = request()->routeIs('user.support.create');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /** Show the create panel without a full navigation. */
    public function startTicket(): void
    {
        $this->resetValidation();
        $this->reset(['subject', 'category', 'priority', 'message', 'attachment']);
        $this->category = 'general';
        $this->priority = 'medium';
        $this->creating = true;
    }

    /** Bail out of the create panel back to the list. */
    public function cancelTicket(): void
    {
        $this->resetValidation();
        $this->reset(['subject', 'category', 'priority', 'message', 'attachment', 'creating']);
    }

    public function createTicket()
    {
        $this->validate([
            'subject'    => 'required|string|min:3|max:200',
            'category'   => 'required|in:' . implode(',', self::CATEGORIES),
            'priority'   => 'required|in:' . implode(',', self::PRIORITIES),
            'message'    => 'required|string|min:5',
            'attachment' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $attachmentPath = $this->attachment
            ? $this->attachment->store('uploads/support/attachments', 'public')
            : null;

        $ticketId = $this->generateTicketId();

        SupportTicket::create([
            'user_id'  => auth()->id(),
            'ticket_id' => $ticketId,
            'category' => $this->category,
            'priority' => $this->priority,
            'subject'  => $this->subject,
            'status'   => 'open',
        ]);

        SupportMessage::create([
            'user_id'    => auth()->id(),
            'ticket_id'  => $ticketId,
            'message'    => $this->message,
            'role'       => 'user',
            'attachment' => $attachmentPath,
        ]);

        Toaster::success(__('Your ticket has been submitted.'));

        $this->reset(['subject', 'category', 'priority', 'message', 'attachment', 'creating']);

        return $this->redirectRoute('user.support.view', ['ticket_id' => $ticketId], navigate: true);
    }

    /** Collision-safe ticket reference, e.g. TKT-9F3KQ2M1. */
    protected function generateTicketId(): string
    {
        do {
            $candidate = 'TKT-' . strtoupper(Str::random(8));
        } while (SupportTicket::where('ticket_id', $candidate)->exists());

        return $candidate;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = SupportTicket::where('user_id', $userId);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('subject', 'like', "%{$this->search}%")
                  ->orWhere('ticket_id', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $tickets = $query->withCount('messages')
            ->orderByDesc('created_at')
            ->paginate(10);

        $base = SupportTicket::where('user_id', $userId);

        return view('livewire.user.support.index', [
            'tickets'         => $tickets,
            'totalCount'      => (clone $base)->count(),
            'openCount'       => (clone $base)->where('status', 'open')->count(),
            'inProgressCount' => (clone $base)->where('status', 'in_progress')->count(),
            'resolvedCount'   => (clone $base)->whereIn('status', ['resolved', 'closed'])->count(),
        ]);
    }
}
