<?php

namespace App\Http\Controllers\Backend\Transfers;

use App\Http\Controllers\Controller;
use App\Mail\TransferCanceledMail;
use App\Models\Transfer;
use App\Models\TransferFile;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class UserTransfersController extends Controller
{
    public function index()
    {
        $unreadTransfers = Transfer::where([['user_id', '!=', null], ['read_status', 0]])->get();
        if (count($unreadTransfers) > 0) {
            foreach ($unreadTransfers as $unreadTransfer) {
                $unreadTransfer->read_status = 1;
                $unreadTransfer->save();
            }
        }
        $byEmailTransfers = Transfer::where([['user_id', '!=', null], ['type', 1], ['status', 1]])->with(['user', 'storageProvider'])->get();
        $byLinkTransfers = Transfer::where([['user_id', '!=', null], ['type', 2], ['status', 1]])->with(['user', 'storageProvider'])->get();
        $canceledTransfers = Transfer::where([['user_id', '!=', null], ['status', 0]])->with(['user', 'storageProvider'])->get();
        return view('backend.transfers.users.index', [
            'byEmailTransfers' => $byEmailTransfers,
            'byLinkTransfers' => $byLinkTransfers,
            'canceledTransfers' => $canceledTransfers,
        ]);
    }

    public function edit($unique_id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', '!=', null]])->with(['user', 'storageProvider'])->firstOrFail();
        return view('backend.transfers.users.edit', ['transfer' => $transfer]);
    }

    public function update(Request $request, $unique_id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', '!=', null]])->with('transferFiles')->first();
        if (is_null($transfer)) {
            toastr()->error(__('Transfer not exists'));
            return back();
        }
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => ['required', 'string', 'max:150'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }
        if (!$transfer->status && !isExpiry($transfer->expiry_at)) {
            toastr()->error(__('Transfer cannot be canceled'));
            return back();
        }
        $updateTransfer = $transfer->update([
            'status' => 0,
            'expiry_at' => Carbon::now(),
            'cancellation_reason' => $request->cancellation_reason,
        ]);
        if ($updateTransfer) {
            if ($request->has('notify_sender')) {
                if (settings('mail_status')) {
                    $subject = str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', mailTemplates('Your transfer has been canceled {transfer_number}', 'transfer cancellation notification'));
                    $details = [
                        'transfer_number' => $transfer->unique_id,
                        'transfer_subject' => $transfer->subject ?? null,
                        'sender' => $transfer->sender_name ?? $transfer->sender_email,
                        'subject' => $subject,
                        'cancellation_reason' => $request->cancellation_reason,
                        'total_files' => $transfer->transferFiles->count(),
                        'total_size' => formatBytes($transfer->transferFiles->sum('size')),
                        'transfer_link' => $transfer->user_id ? route('user.transfers.show', $transfer->unique_id) : null,
                        'files' => $transfer->transferFiles,
                    ];
                    Mail::to($transfer->sender_email)->send(new TransferCanceledMail($details));
                }
                $title = str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', lang('Transfer canceled {transfer_number}', 'notifications'));
                $image = asset('images/icons/transfer-canceled.png');
                $link = route('user.transfers.show', $transfer->unique_id);
                userNotify($transfer->user_id, $title, $image, $link);
            }
            foreach ($transfer->transferFiles as $transferFile) {
                $handler = $transferFile->storageProvider->handler;
                if ($handler::delete($transferFile->path)) {
                    $transferFile->delete();
                }
            }
            toastr()->success(__('Transfer Canceled Successfully'));
            return back();
        }
    }

    public function destroy($unique_id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', '!=', null]])->with('transferFiles')->firstOrFail();
        foreach ($transfer->transferFiles as $transferFile) {
            $handler = $transferFile->storageProvider->handler;
            $deleteFile = $handler::delete($transferFile->path);
        }
        $link = route('user.transfers.show', $transfer->unique_id);
        $userNotifications = UserNotification::where([['user_id', $transfer->user_id], ['link', $link]])->get();
        foreach ($userNotifications as $userNotification) {
            $userNotification->delete();
        }
        $transfer->delete();
        toastr()->success(__('Transfer deleted successfully'));
        return back();
    }

    public function download($unique_id, $id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', '!=', null], ['expiry_at', '>', Carbon::now()]])
            ->orWhere([['unique_id', $unique_id], ['user_id', '!=', null], ['expiry_at', null]])
            ->withCount('transferFiles')
            ->firstOrFail();
        $transferFile = TransferFile::where([['id', unhashid($id)], ['user_id', '!=', null], ['transfer_id', $transfer->id]])->firstOrFail();
        try {
            $handler = $transferFile->storageProvider->handler;
            $download = $handler::download($transferFile);
            if ($transferFile->storageProvider->symbol != "local") {
                return redirect($download);
            } else {
                return $download;
            }
        } catch (Exception $e) {
            toastr()->error(__('There was a problem while trying to download the file'));
            return redirect()->route('admin.transfers.users.index');
        }
    }

    public function deleteFile(Request $request, $unique_id, $id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', '!=', null], ['expiry_at', '>', Carbon::now()]])
            ->orWhere([['unique_id', $unique_id], ['user_id', '!=', null], ['expiry_at', null]])
            ->withCount('transferFiles')
            ->firstOrFail();
        abort_if($transfer->transfer_files_count < 2, 401);
        $transferFile = TransferFile::where([['id', unhashid($id)], ['user_id', '!=', null], ['transfer_id', $transfer->id]])->firstOrFail();
        $handler = $transferFile->storageProvider->handler;
        $deleteFile = $handler::delete($transferFile->path);
        if ($deleteFile) {
            $transferFile->delete();
            toastr()->success(__('File deleted successfully'));
            return back();
        }
    }
}
