<?php

namespace App\Livewire\Admin\Support;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Title('Support Ticket View')]
class TicketView extends Component
{
    use WithFileUploads;

    public SupportTicket $ticket;
    public $status;
    public $responseMessage;
    public $attachment;

    public function mount($ticket_id)
    {
        $this->ticket = SupportTicket::where('ticket_id', $ticket_id)->firstOrFail();
        $this->status = $this->ticket->status;
    }

    public function submitResponse()
    {
        $this->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'responseMessage' => 'required|string|min:1',
            'attachment' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('uploads/support/attachments', 'public');
        }

        SupportMessage::create([
            'user_id' => auth()->id(),
            'ticket_id' => $this->ticket->ticket_id,
            'message' => $this->responseMessage,
            'role' => 'admin',
            'attachment' => $attachmentPath ? $attachmentPath : null,
        ]);

        $this->ticket->update(['status' => $this->status]);

        $this->reset(['responseMessage', 'attachment']);

        session()->flash('message', 'Response sent successfully.');
    }

    public function render()
    {
        $messages = $this->ticket->messages()->orderBy('created_at', 'asc')->get();
        
        return view('livewire.admin.support.view', [
            'messages' => $messages
        ]);
    }
}
