<?php

namespace App\Http\Controllers\Frontend\Transfer;

use App\Http\Controllers\Controller;
use App\Models\StorageProvider;
use App\Models\Transfer;
use App\Models\TransferFile;
use App\Models\Upload;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Str;
use Validator;

class CreateLinkController extends Controller
{
    public function process(Request $request)
    {
        try {
            if (!$request->has('files')) {
                return response()->json(['error' => lang('No files uploaded', 'upload zone')]);
            }
            if ($request->has('custom_link') && !is_null($request->custom_link)) {
                if (!preg_match('/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', $request->custom_link)) {
                    return response()->json(['error' => lang('Custom link can only contain Letters or Numbers or Dashes', 'upload zone')]);
                }
            } else {
                $request->custom_link = null;
            }
            $validator = Validator::make($request->all(), [
                'sender_email' => ['required', 'email', 'max:255'],
                'subject' => ['nullable', 'string', 'max:255'],
                'custom_link' => ['nullable', 'alpha_dash', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/', 'min:6', 'max:255', 'unique:transfers,link'],
                'password' => ['max:255'],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    return response()->json(['error' => $error]);
                }
            }
            $subscription = subscription();
            if (!$subscription->plan->transfer_link) {
                return response()->json(['error' => lang('Create a link feature not available for your subscription', 'upload zone')]);
            }
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
            $userId = (Auth::user()) ? Auth::user()->id : null;
            $link = ($request->has('custom_link') && !is_null($request->custom_link)) ? $request->custom_link : Str::random(14);
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
                'subject' => $request->subject,
                'password' => $request->password,
                'download_notify' => $request->download_notify,
                'expiry_notify' => $request->expiry_notify,
                'type' => 2,
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
