<?php

namespace App\Livewire\User\Support;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

#[Title('Support Ticket')]
class SupportView extends Component
{
    use WithFileUploads;

    public SupportTicket $ticket;
    public string $replyMessage = '';
    public $attachment;

    public function mount($ticket_id): void
    {
        // Scope to the owner so users can only ever open their own tickets.
        $this->ticket = SupportTicket::where('ticket_id', $ticket_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function submitReply(): void
    {
        // Closed tickets are read-only — a fresh ticket is the right path.
        if ($this->ticket->status === 'closed') {
            Toaster::error(__('This ticket is closed. Please open a new ticket if you still need help.'));
            return;
        }

        $this->validate([
            'replyMessage' => 'required|string|min:1',
            'attachment'   => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $attachmentPath = $this->attachment
            ? $this->attachment->store('uploads/support/attachments', 'public')
            : null;

        SupportMessage::create([
            'user_id'    => auth()->id(),
            'ticket_id'  => $this->ticket->ticket_id,
            'message'    => $this->replyMessage,
            'role'       => 'user',
            'attachment' => $attachmentPath,
        ]);

        // A user reply on a resolved ticket reopens the conversation.
        if ($this->ticket->status === 'resolved') {
            $this->ticket->update(['status' => 'open']);
        }

        $this->reset(['replyMessage', 'attachment']);

        Toaster::success(__('Your reply has been sent.'));
    }

    public function render()
    {
        $messages = $this->ticket->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.user.support.view', [
            'messages' => $messages,
        ]);
    }
}
