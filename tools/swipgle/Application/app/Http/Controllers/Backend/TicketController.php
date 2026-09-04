<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SupportAttachment;
use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\NewTicketCreatedNotification;
use App\Notifications\NewTicketReplyNotification;
use Illuminate\Http\Request;
use Notification;
use Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $status = null)
    {
        if ($request->input('search')) {
            $q = $request->input('search');
            $tickets = SupportTicket::join('users', 'support_tickets.user_id', '=', 'users.id')
                ->where('support_tickets.ticket_number', 'like', '%' . $q . '%')
                ->OrWhere('support_tickets.subject', 'like', '%' . $q . '%')
                ->OrWhere('support_tickets.priority', 'like', '%' . $q . '%')
                ->OrWhere('support_tickets.status', 'like', '%' . $q . '%')
                ->OrWhere('users.firstname', 'like', '%' . $q . '%')
                ->OrWhere('users.lastname', 'like', '%' . $q . '%')
                ->OrWhere('users.email', 'like', '%' . $q . '%')
                ->OrWhere('users.username', 'like', '%' . $q . '%')
                ->select('*')
                ->get();
        } else {
            if ($status) {
                $tickets = SupportTicket::where('status', $this->ticketStatus($status))->with('user')->orderbyDesc('id')->paginate(12);
            } else {
                $tickets = SupportTicket::with('user')->orderbyDesc('id')->paginate(12);
                $status = 'All';
            }
        }
        return view('backend.support.tickets.index', [
            'tickets' => $tickets,
            'status' => $status,
        ]);
    }

    /**
     * Get status number.
     *
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::select('id', 'firstname', 'lastname', 'email')->orderbyDesc('id')->get();
        return view('backend.support.tickets.create', ['users' => $users]);
    }

    /**
     * Get user information on select change
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request)
    {
        $user = User::findOrFail($request->id);
        return response()->json([
            'name' => $user->firstname . ' ' . $user->lastname,
            'avatar' => asset($user->avatar),
            'email' => $user->email,
            'joined_date' => $user->created_at->diffForHumans(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $files = $request->file('attachments');
        $allowedExts = ['jpg', 'png', 'jpeg', 'pdf'];
        $ticketNumber = rand(100000, 999999);

        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'user' => ['required', 'integer'],
            'priority' => ['required', 'integer', 'min:0', 'max:3'],
            'attachments' => [
                'max:2048',
                function ($attribute, $value, $fail) use ($files, $allowedExts) {
                    foreach ($files as $file) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (($file->getSize() / 1000000) > 2) {
                            return $fail(__('Max file size is 2MB'));
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__('Supported types : JPG, JPEG, PNG, PDF'));
                        }
                    }
                    if (count($files) > 5) {
                        return $fail(__('Max 5 files can be uploaded'));
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

        if ($request->has('sendMail') && !settings('mail_status')) {
            toastr()->error(__('SMTP is not enabled'));
            return back()->withInput();
        }

        $user = User::find($request->user);
        if ($user == null) {
            toastr()->error(__('User does not exist'));
            return back();
        }
        $createTicket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 2,
        ]);
        if ($createTicket) {
            $createReply = SupportReply::create([
                'admin_id' => adminAuthInfo()->id,
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
                    } catch (\Exception $e) {
                        toastr()->error($file . __(' cannot be uploaded'));
                        return back()->withInput();
                    }
                }
            }
            if ($request->has('sendMail')) {
                $details = ['ticketNumber' => $createTicket->ticket_number];
                Notification::send($user, new NewTicketCreatedNotification($details));
            }

            $user_id = $user->id;
            $title = str_replace('{ticket_number}', '[#' . $createTicket->ticket_number . ']', lang('New Ticket Created {ticket_number}', 'notifications'));
            $image = asset('images/icons/tickets.png');
            $link = route('user.tickets.view', $createTicket->ticket_number);
            userNotify($user_id, $title, $image, $link);

            toastr()->success(__('Created Successfully'));
            return redirect()->route('tickets.show', $createTicket->ticket_number);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SupportTicket  $supportTicket
     * @return \Illuminate\Http\Response
     */
    public function show($ticket_number)
    {
        $ticket = SupportTicket::where('ticket_number', $ticket_number)->with('user')->first();
        if ($ticket != null) {
            $replies = SupportReply::where('support_ticket_id', $ticket->id)->with(['admin', 'supportAttachments'])->get();
            return view('backend.support.tickets.show', ['ticket' => $ticket, 'replies' => $replies]);
        } else {
            return abort(404);
        }

    }

    /**
     * Update ticket status
     *
     * @return \Illuminate\Http\Response
     */
    public function closeTicket(Request $request, $ticket_number)
    {
        $ticket = SupportTicket::where('ticket_number', $ticket_number)->first();
        if ($ticket == null) {
            toastr()->error(__('Ticket error'));
            return back();
        }
        $ticket->update(['status' => 3]);
        $user_id = $ticket->user_id;
        $title = str_replace('{ticket_number}', '[#' . $ticket->ticket_number . ']', lang('Ticket Closed {ticket_number}', 'notifications'));
        $image = asset('images/icons/tickets.png');
        $link = route('user.tickets.view', $ticket->ticket_number);
        userNotify($user_id, $title, $image, $link);
        toastr()->success(__('Closed Successfully'));
        return back();
    }

    /**
     * Download Attachments
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadAttachments($ticket_number, $id)
    {
        $attachment = SupportAttachment::findOrFail(decrypt($id));
        $filename = str_replace('uploads/tickets/', '', $attachment->attachment);
        $mime = mime_content_type($attachment->attachment);
        $headers = [
            "Content-Disposition" => "attachment; filename=" . $filename,
            "Content-Type" => $mime,
        ];
        return \Response::download($attachment->attachment, $filename, $headers);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SupportTicket  $supportTicket
     * @return \Illuminate\Http\Response
     */
    public function edit(SupportTicket $ticket)
    {
        return abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportTicket  $supportTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        return abort(404);
    }

    /**
     * Reply on ticket
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $ticket_number = Ticket number
     * @return \Illuminate\Http\Response
     */
    public function ticketReply(Request $request, $ticket_number)
    {
        $ticket = SupportTicket::where('ticket_number', $ticket_number)->first();
        if ($ticket == null) {
            toastr()->error(__('Something went wrong please try again'));
            return back();
        }

        $user = User::find($ticket->user_id);
        if ($user == null) {
            toastr()->error(__('User not exist'));
            return back();
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
                            return $fail(__('Max file size is 2MB'));
                        }
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__('Supported types : JPG, JPEG, PNG, PDF'));
                        }
                    }
                    if (count($files) > 5) {
                        return $fail(__('Max 5 files can be uploaded'));
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
            'admin_id' => adminAuthInfo()->id,
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
                    } catch (\Exception $e) {
                        toastr()->error($file . __(' cannot be uploaded'));
                        return back()->withInput();
                    }
                }
            }
            if (settings('mail_status')) {
                if ($ticket->status != 2) {
                    $details = ['ticketNumber' => $ticket->ticket_number];
                    Notification::send($user, new NewTicketReplyNotification($details));
                }
            }

            $notification = UserNotification::where([
                ['user_id', $user->id], ['link', route('user.tickets.view', $ticket->ticket_number)], ['status', 0]])->get();
            if ($notification->count() == 0) {
                $user_id = $user->id;
                $title = str_replace('{ticket_number}', '[#' . $ticket->ticket_number . ']', lang('Ticket {ticket_number} New Reply', 'notifications'));
                $image = asset('images/icons/tickets.png');
                $link = route('user.tickets.view', $ticket->ticket_number);
                userNotify($user_id, $title, $image, $link);
            }

            $ticket->update(['status' => 2]);
            toastr()->success(__('Sent Successfully'));
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SupportTicket  $supportTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupportTicket $ticket)
    {
        if (demoMode()) {
            toastr()->error('Some features are disabled in the demo version');
            return back();
        }
        $replies = SupportReply::where('support_ticket_id', $ticket->id)->get();
        if ($replies->count() > 0) {
            foreach ($replies as $reply) {
                $attachments = SupportAttachment::where('support_reply_id', $reply->id)->get();
                if ($attachments->count() > 0) {
                    foreach ($attachments as $attachment) {
                        removeFile($attachment->attachment);
                    }
                }
            }
        }
        $link = route('user.tickets.view', $ticket->ticket_number);
        $userNotifications = UserNotification::where([['user_id', $ticket->user_id], ['link', $link]])->get();
        foreach ($userNotifications as $userNotification) {
            $userNotification->delete();
        }
        $ticket->delete();
        toastr()->success(__('Deleted Successfully'));
        return back();
    }
}
