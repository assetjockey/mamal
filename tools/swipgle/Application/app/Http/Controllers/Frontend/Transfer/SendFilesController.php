<?php

namespace App\Http\Controllers\Frontend\Transfer;

use App\Http\Controllers\Controller;
use App\Mail\TransferMail;
use App\Models\StorageProvider;
use App\Models\Transfer;
use App\Models\TransferFile;
use App\Models\Upload;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Str;
use Validator;

class SendFilesController extends Controller
{
    public function process(Request $request)
    {
        lang('No files uploaded', 'upload zone');
        lang('Send to field is invalid', 'upload zone');
        try {
            if (!$request->has('files')) {
                return response()->json(['error' => lang('No files uploaded', 'upload zone')]);
            }
            $validator = Validator::make($request->all(), [
                'sender_email' => ['required', 'email', 'max:255'],
                'sender_name' => ['nullable', 'string', 'max:255'],
                'send_to' => ['required'],
                'subject' => ['nullable', 'string', 'max:255'],
                'message' => ['nullable', 'string', 'max:1000'],
                'password' => ['max:255'],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    return response()->json(['error' => $error]);
                }
            }
            $subscription = subscription();
            $files = [];
            $transferSize = 0;
            foreach ($request->input('files') as $file) {
                if (is_null($file)) {
                    return response()->json(['error' => lang('File error', 'upload zone')]);
                }
                if (Auth::user()) {
                    $findFile = Upload::where([['id', unhashid($file)], ['user_id', Auth::user()->id]])->first();
                } else {
                    $findFile = Upload::where([['id', unhashid($file)], ['ip', vIpInfo()->ip], ['user_id', null]])->first();
                }
                if (is_null($findFile)) {
                    return response()->json(['error' => lang('File error', 'upload zone')]);
                }
                $files[] = $findFile;
                $transferSize += $findFile->size;
            }
            if (!is_null($subscription->plan->transfer_size_number)) {
                if ($transferSize > $subscription->plan->transfer_size_number) {
                    return response()->json(['error' => str_replace('{maxTransferSize}', subscription()->plan->transfer_size,
                        lang('Max size per transfer : {maxTransferSize}.', 'upload zone'))]);
                }
            }
            $emails = (object) explode(',', $request->send_to);
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json(['error' => lang('Send to field is invalid', 'upload zone')]);
                }
            }
            $userId = (Auth::user()) ? Auth::user()->id : null;
            $link = Str::random(14);
            $password = $request->password;
            if ($request->has('password_checkbox') && $request->password != null) {
                if ($subscription->plan->transfer_password) {
                    $request->password = Hash::make($request->password);
                } else {
                    $request->password = null;
                }
            } else {
                $request->password = null;
            }
            $request->download_notify = 0;
            $request->expiry_notify = 0;
            if ($request->has('notification_checkbox')) {
                if ($request->has('download_notify') or $request->has('expiry_notify')) {
                    if ($subscription->plan->transfer_notify) {
                        $request->download_notify = ($request->has('download_notify')) ? 1 : 0;
                        $request->expiry_notify = ($request->has('expiry_notify')) ? 1 : 0;
                    } else {
                        $request->download_notify = 0;
                        $request->expiry_notify = 0;
                    }
                }
            }
            if ($request->has('expiry_checkbox') && $request->expiry_at != null) {
                if (!$subscription->plan->transfer_expiry) {
                    if ($subscription->plan->transfer_interval_number) {
                        $request->expiry_at = Carbon::now()->addDays($subscription->plan->transfer_interval_number);
                    }
                }
                $expiryDate = Carbon::parse($request->expiry_at);
                if ($expiryDate < Carbon::now()) {
                    return response()->json(['error' => lang('Expiry date is invalid', 'upload zone')]);
                } elseif (!is_null($subscription->plan->transfer_interval_number)
                    && $expiryDate > Carbon::now()->addDays($subscription->plan->transfer_interval_number)) {
                    return response()->json(['error' => str_replace('{files_duration}', $subscription->plan->transfer_interval_days,
                        lang('Expiry date must be equal or less than {files_duration}', 'upload zone'))]);
                } elseif (Carbon::now()->addMinutes(10) > $expiryDate) {
                    return response()->json(['error' => lang('Expiry date must be 10 minutes minimum', 'upload zone')]);
                } else {
                    $request->expiry_at = $expiryDate;
                }
            } else {
                if ($subscription->plan->transfer_interval_number) {
                    $request->expiry_at = Carbon::now()->addDays($subscription->plan->transfer_interval_number);
                }
            }
            if ($request->expiry_notify && is_null($request->expiry_at)) {
                return response()->json(['error' => lang('You cannot use notify when expiry when transfer expiry time is unlimited', 'upload zone')]);
            }
            $storageProvider = StorageProvider::where([['symbol', env('FILESYSTEM_DRIVER')], ['status', 1]])->first();
            if (is_null($storageProvider)) {
                return response()->json(['error' => lang('Unavailable storage provider', 'upload zone')]);
            }
            $createTransfer = Transfer::create([
                'user_id' => $userId,
                'ip' => vIpInfo()->ip,
                'storage_provider_id' => $storageProvider->id,
                'unique_id' => randomCode(16),
                'link' => $link,
                'sender_email' => $request->sender_email,
                'sender_name' => $request->sender_name,
                'emails' => $emails,
                'subject' => $request->subject,
                'message' => $request->message,
                'password' => $request->password,
                'download_notify' => $request->download_notify,
                'expiry_notify' => $request->expiry_notify,
                'type' => 1,
                'expiry_at' => $request->expiry_at,
            ]);
            if ($createTransfer) {
                foreach ($files as $file) {
                    $transferFile = new TransferFile();
                    $transferFile->user_id = $file->user_id;
                    $transferFile->transfer_id = $createTransfer->id;
                    $transferFile->storage_provider_id = $file->storage_provider_id;
                    $transferFile->ip = $file->ip;
                    $transferFile->name = $file->name;
                    $transferFile->filename = $file->filename;
                    $transferFile->extension = $file->extension;
                    $transferFile->mime = $file->mime;
                    $transferFile->size = $file->size;
                    $transferFile->path = $file->path;
                    $transferFile->save();
                    $file->delete();
                }
                if (settings('mail_status')) {
                    $transferSubject = ($createTransfer->subject) ? ' (' . $createTransfer->subject . ')' : '';
                    $subject = mailTemplates('You have received some files', 'transfer files notification') . $transferSubject;
                    $details = [
                        'sender' => $createTransfer->sender_name ?? $createTransfer->sender_email,
                        'subject' => $subject,
                        'message' => $createTransfer->message,
                        'password' => $password,
                        'total_files' => count($files),
                        'total_size' => formatBytes($transferSize),
                        'expiry_at' => ($createTransfer->expiry_at) ? vDate($createTransfer->expiry_at) : null,
                        'transfer_link' => route('transfer.download.index', $createTransfer->link),
                        'files' => $files,
                    ];
                    foreach ($createTransfer->emails as $key => $value) {
                        Mail::to($value)->send(new TransferMail($details));
                    }
                }
                return response()->json([
                    'transfer_download_link' => url('d/' . $createTransfer->link),
                    'view_transfer_link' => (Auth::user()) ? route('user.transfers.show', $createTransfer->unique_id) : null,
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => lang('Transfer error', 'upload zone')]);
        }
    }
}
