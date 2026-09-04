<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\TransferFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        if ($request->input('search')) {
            $q = $request->input('search');
            $transfers = Transfer::where([['unique_id', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['link', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['sender_email', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['sender_name', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['emails', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['subject', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['message', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->withCount('transferFiles')
                ->orderbyDesc('id')
                ->paginate(20);
            $transfers->appends(['q' => $q]);
        } else {
            $transfers = Transfer::where('user_id', userAuthInfo()->id)->withCount('transferFiles')->orderbyDesc('id')->paginate(20);
        }
        $activeTransfersCount = Transfer::where([['user_id', userAuthInfo()->id], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->OrWhere([['user_id', userAuthInfo()->id], ['expiry_at', null], ['status', 1]])
            ->count();
        $expiredTransfersCount = Transfer::where([['user_id', userAuthInfo()->id], ['expiry_at', '<', Carbon::now()], ['status', 1]])->count();
        $canceledTransfersCount = Transfer::where([['user_id', userAuthInfo()->id], ['status', 0]])->count();
        return view('frontend.user.transfers.index', [
            'transfers' => $transfers,
            'activeTransfersCount' => $activeTransfersCount,
            'expiredTransfersCount' => $expiredTransfersCount,
            'canceledTransfersCount' => $canceledTransfersCount,
        ]);
    }

    public function show($unique_id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', userAuthInfo()->id]])->with('transferFiles')->withCount('transferFiles')->firstOrFail();
        return view('frontend.user.transfers.show', ['transfer' => $transfer]);
    }

    public function update(Request $request, $unique_id)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['max:255'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', '>', Carbon::now()]])
            ->orWhere([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', null]])
            ->withCount('transferFiles')
            ->first();
        if (is_null($transfer)) {
            toastr()->error(lang('Transfer not exists or expired', 'user'));
            return back();
        }
        $subscription = subscription();
        if (!$subscription->plan->transfer_notify && !$subscription->plan->transfer_password) {
            return back();
        }
        if ($request->has('transfer_password') && !is_null($request->transfer_password)) {
            if ($subscription->plan->transfer_password) {
                $request->transfer_password = Hash::make($request->transfer_password);
            } else {
                toastr()->error(lang('Setting password feature not available for your subscription', 'upload zone'));
                return back();
            }
        } else {
            $request->transfer_password = null;
        }
        $request->download_notify = 0;
        $request->expiry_notify = 0;
        if ($request->has('download_notify') or $request->has('expiry_notify')) {
            if ($subscription->plan->transfer_notify) {
                $request->download_notify = ($request->has('download_notify')) ? 1 : 0;
                $request->expiry_notify = ($request->has('expiry_notify')) ? 1 : 0;
            } else {
                toastr()->error(lang('The notify on download and expiry feature not available for your subscription', 'upload zone'));
                return back();
            }
        }
        $request->downloaded_at = ($request->download_notify) ? null : $transfer->downloaded_at;
        $transferUpdate = $transfer->update([
            'password' => $request->transfer_password,
            'download_notify' => $request->download_notify,
            'expiry_notify' => $request->expiry_notify,
            'downloaded_at' => $request->downloaded_at,
        ]);
        if ($transferUpdate) {
            toastr()->success(lang('Transfer updated successfully', 'user'));
            return back();
        }
    }

    public function downloadFiles($unique_id, $id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', '>', Carbon::now()]])
            ->orWhere([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', null]])
            ->withCount('transferFiles')
            ->firstOrFail();
        $transferFile = TransferFile::where([['id', unhashid($id)], ['user_id', userAuthInfo()->id], ['transfer_id', $transfer->id]])->firstOrFail();
        try {
            $handler = $transferFile->storageProvider->handler;
            $download = $handler::download($transferFile);
            if ($transferFile->storageProvider->symbol != "local") {
                return redirect($download);
            } else {
                return $download;
            }
        } catch (Exception $e) {
            toastr()->error(lang('There was a problem while trying to download the file', 'download page'));
            return redirect()->route('user.dashboard');
        }
    }

    public function deleteFiles(Request $request, $unique_id, $id)
    {
        $transfer = Transfer::where([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', '>', Carbon::now()]])
            ->orWhere([['unique_id', $unique_id], ['user_id', userAuthInfo()->id], ['expiry_at', null]])
            ->withCount('transferFiles')
            ->first();
        if (is_null($transfer)) {
            toastr()->error(lang('Transfer not exists or expired', 'user'));
            return back();
        }
        if ($transfer->transfer_files_count < 2) {
            toastr()->error(lang('Transfer must have one file at least', 'user'));
            return back();
        }
        $transferFile = TransferFile::where([['id', unhashid($id)], ['user_id', userAuthInfo()->id], ['transfer_id', $transfer->id]])->first();
        if (is_null($transferFile)) {
            toastr()->error(lang('Transfer file not exists', 'user'));
            return back();
        }
        $handler = $transferFile->storageProvider->handler;
        $deleteFile = $handler::delete($transferFile->path);
        if ($deleteFile) {
            $transferFile->delete();
            toastr()->success(lang('File deleted successfully', 'user'));
            return back();
        }
    }
}
