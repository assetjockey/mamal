<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\SupportAttachment;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Validator;

class TicketController extends Controller
{
    public function index(Request $request, $status = null)
    {
        if ($request->input('search')) {
            $q = $request->input('search');
            $tickets = SupportTicket::where([['ticket_number', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['subject', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['priority', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['status', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->paginate(20);
            $tickets->appends(['q' => $q]);
        } else {
            if ($status) {
                $tickets = SupportTicket::where([['status', $this->ticketStatus($status)], ['user_id', userAuthInfo()->id]])->with('user')->orderbyDesc('id')->paginate(20);
            } else {
                $tickets = SupportTicket::where('user_id', userAuthInfo()->id)->orderbyDesc('updated_at')->paginate(20);
            }
        }
        return view('frontend.user.tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    protected function ticketStatus($status)
    {
        if ($status == 'opened') {
            $status = 0;
        } elseif ($status == 'answered') {
            $status = 1;
        } elseif ($status == 'replied') {
            $status = 2;
        } elseif ($status == 'closed') {
            $status = 3;
        } else {
            $status = null;
        }
        return $status;
    }

    public function create()
    {
        return view('frontend.user.tickets.create');
    }

    public function store(Request $request)
    {
        $files = $request->file('attachments');
        $allowedExts = ['jpg', 'png', 'jpeg', 'pdf'];
        $ticketNumber = rand(100000, 999999);

        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'priority' => ['required', 'integer', 'min:0', 'max:3'],
            'attachments' => [
                'max:2048',
                function ($attribute, $value, $fail) use ($files, $allowedExts) {
                    foreach ($files as $file) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (($file->getSize() / 1000000) > 2) {
                            return $fail(lang('Max file size is 2MB', 'tickets'));
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(lang('Supported types', 'tickets') . ' : JPG, JPEG, PNG, PDF');
                        }
                    }
                    if (count($files) > 5) {
                        return $fail(lang('Max 5 files can be uploaded', 'tickets'));
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $createTicket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => userAuthInfo()->id,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 0,
        ]);

        if ($createTicket) {
            $createReply = SupportReply::create([
                'support_ticket_id' => $createTicket->id,
                'message' => $request->message,
            ]);
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        $attachment = new SupportAttachment();
                        $attachment->support_reply_id = $createReply->id;
                        $attachment->attachment = vFileUpload($file, 'uploads/tickets/');
                        $attachment->save();
                    } catch (\Exception$e) {
                        toastr()->error($file . ' ' . lang('cannot be uploaded', 'tickets'));
                        return back()->withInput();
                    }
                }
            }

            $title = "New Ticket Opened [#" . $createTicket->ticket_number . "]";
            $image = asset('images/icons/tickets.png');
            $link = route('tickets.show', $createTicket->ticket_number);
            adminNotify($title, $image, $link);

            toastr()->success(lang('Ticket Created Successfully', 'tickets'));
            return redirect()->route('user.tickets.view', $createTicket->ticket_number);
        }
    }

    public function view($ticket_number)
    {
        $ticket = SupportTicket::where([['ticket_number', $ticket_number], ['user_id', userAuthInfo()->id]])->with('user')->first();
        if ($ticket != null) {
            $replies = SupportReply::where('support_ticket_id', $ticket->id)->with(['admin', 'supportAttachments'])->get();
            return view('frontend.user.tickets.view', ['ticket' => $ticket, 'replies' => $replies]);
        } else {
            return abort(404);
        }
    }

    public function ticketReply(Request $request, $ticket_number)
    {
        $ticket = SupportTicket::where([['ticket_number', $ticket_number], ['user_id', userAuthInfo()->id]])->first();
        if ($ticket == null) {
            return redirect()->route('user.tickets');
        }

        $files = $request->file('attachments');
        $allowedExts = ['jpg', 'png', 'jpeg', 'pdf'];

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string'],
            'attachments' => [
                'max:2048',
                function ($attribute, $value, $fail) use ($files, $allowedExts) {
                    foreach ($files as $file) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (($file->getSize() / 1000000) > 2) {
                            return $fail(lang('Max file size is 2MB', 'tickets'));
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(lang('Supported types', 'tickets') . ' : JPG, JPEG, PNG, PDF');
                        }
                    }
                    if (count($files) > 5) {
                        return $fail(lang('Max 5 files can be uploaded', 'tickets'));
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $createReply = SupportReply::create([
            'support_ticket_id' => $ticket->id,
            'message' => $request->message,
        ]);

        if ($createReply) {
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        $attachment = new SupportAttachment();
                        $attachment->support_reply_id = $createReply->id;
                        $attachment->attachment = vFileUpload($file, 'uploads/tickets/');
                        $attachment->save();
                    } catch (Exception $e) {
                        toastr()->error($file . ' ' . lang('cannot be uploaded', 'tickets'));
                        return back()->withInput();
                    }
                }
            }

            $notification = AdminNotification::where([['link', route('tickets.show', $ticket->ticket_number)], ['status', 0]])->get();
            if ($notification->count() == 0) {
                $title = "[Ticket#" . $ticket->ticket_number . "] New Reply";
                $image = asset('images/icons/tickets.png');
                $link = route('tickets.show', $ticket->ticket_number);
                adminNotify($title, $image, $link);
            }

            if ($ticket->status != 0) {
                $ticket->update(['status' => 1]);
            }
            toastr()->success(lang('Reply Sent Successfully', 'tickets'));
            return back();
        }
    }

    public function downloadAttachments($ticket_number, $id)
    {
        $ticket = SupportTicket::where([['ticket_number', $ticket_number], ['user_id', userAuthInfo()->id]])->first();
        if ($ticket == null) {
            return redirect()->route('user.tickets');
        }
        $attachment = SupportAttachment::find(decrypt($id));
        if ($attachment == null) {
            return redirect()->route('user.tickets');
        }
        $reply = SupportReply::where([['id', $attachment->support_reply_id], ['support_ticket_id', $ticket->id]])->first();
        if ($reply == null) {
            return redirect()->route('user.tickets');
        }
        $filename = str_replace('uploads/tickets/', '', $attachment->attachment);
        $mime = mime_content_type($attachment->attachment);
        $headers = [
            "Content-Disposition" => "attachment; filename=" . $filename,
            "Content-Type" => $mime,
        ];
        return \Response::download($attachment->attachment, $filename, $headers);

    }
}
