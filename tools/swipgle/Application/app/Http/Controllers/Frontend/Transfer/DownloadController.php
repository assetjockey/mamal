<?php

namespace App\Http\Controllers\Frontend\Transfer;

use App\Http\Controllers\Controller;
use App\Mail\TransferDownloadedMail;
use App\Models\Transfer;
use App\Models\TransferFile;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Validator;
use Zip;

class DownloadController extends Controller
{
    public function index($link)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->with('storageProvider')->firstOrFail();
        if (!is_null($transfer->password) && !Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return redirect()->route('transfer.download.password', $transfer->link);
        }
        $transferFiles = TransferFile::where('transfer_id', $transfer->id)->get();
        $transferFilesTotalSize = TransferFile::where('transfer_id', $transfer->id)->sum('size');
        return view('frontend.download', [
            'transfer' => $transfer,
            'transferFiles' => $transferFiles,
            'transferFilesTotalSize' => formatBytes($transferFilesTotalSize),
        ]);
    }

    public function showPasswordForm($link)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->firstOrFail();
        if (is_null($transfer->password) or Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return redirect()->route('transfer.download.index', $transfer->link);
        }
        return view('frontend.download', [
            'transfer' => $transfer,
            'password' => true,
        ]);
    }

    public function unlockTransfer(Request $request, $link)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->first();
        if (is_null($transfer)) {
            toastr()->error(lang('Transfer not found', 'password page'));
            return back();
        }
        if (is_null($transfer->password)) {
            return redirect()->route('transfer.download.index', $transfer->link);
        }
        if (Hash::check($request->password, $transfer->password)) {
            $request->session()->put($transfer->link . '_' . sha1($transfer->password), $transfer->link);
            return redirect()->route('transfer.download.index', $transfer->link);
        } else {
            toastr()->error(lang('Incorrect password', 'password page'));
            return back();
        }
    }

    public function requestDownloadLink(Request $request, $link)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->first();
        if (is_null($transfer)) {
            return response()->json(['error' => lang('Transfer not found', 'download page')]);
        }
        if (!is_null($transfer->password) && !Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return response()->json(['error' => lang('Unauthorized access', 'download page')]);
        }
        $transferFile = TransferFile::where([['id', unhashid($request->id)], ['transfer_id', $transfer->id]])->with('storageProvider')->first();
        if (is_null($transferFile)) {
            return response()->json(['error' => lang('Requested file not exists', 'download page')]);
        }
        $request->session()->put(sha1($transferFile->id), hashid($transferFile->id));
        return response()->json(['type' => 'success', 'download_link' => route('transfer.download.single.file', [$transfer->link, hashid($transferFile->id)])]);
    }

    public function download($link, $file_id)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->with('transferFiles')->first();
        if (!is_null($transfer->password) && !Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return redirect()->route('transfer.download.password', $transfer->link);
        }
        $transferFile = TransferFile::where('id', unhashid($file_id))->with('storageProvider')->firstOrFail();
        abort_if($transfer->id != $transferFile->transfer_id, 404);
        abort_if(!Session::has(sha1($transferFile->id)), 401);
        try {
            $handler = $transferFile->storageProvider->handler;
            $download = $handler::download($transferFile);
            Session::forget(sha1($transferFile->id));
            if (is_null($transfer->downloaded_at)) {
                $transfer->update(['downloaded_at' => Carbon::now()]);
                if ($transfer->download_notify) {
                    if (settings('mail_status')) {
                        $details = [
                            'subject' => str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', mailTemplates('Your transferred files has been downloaded {transfer_number}', 'transferred files downloaded notification')),
                            'transfer_number' => $transfer->unique_id,
                            'sender' => $transfer->sender_name ?? $transfer->sender_email,
                            'transfer_subject' => $transfer->subject ?? null,
                            'total_files' => $transfer->transferFiles->count(),
                            'total_size' => formatBytes($transfer->transferFiles->sum('size')),
                            'transfer_link' => $transfer->user_id ? route('user.transfers.show', $transfer->unique_id) : null,
                            'files' => $transfer->transferFiles,
                            'downloaded_at' => vDate($transfer->downloaded_at),
                            'expiry_at' => vDate($transfer->expiry_at),
                        ];
                        $this->notifyOnDownload($transfer->sender_email, $details);
                    }
                }
            }
            $transferFile->increment('downloads', 1);
            if ($transferFile->storageProvider->symbol != "local") {
                return redirect($download);
            } else {
                return $download;
            }
        } catch (Exception $e) {
            toastr()->error(lang('There was a problem while trying to download the file', 'download page'));
            return redirect()->route('transfer.download.index', $transfer->link);
        }
    }

    public function requestDownloadAllLink($link)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->first();
        if (is_null($transfer)) {
            return response()->json(['error' => lang('Transfer not found', 'download page')]);
        }
        if (!is_null($transfer->password) && !Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return response()->json(['error' => lang('Unauthorized access', 'download page')]);
        }
        Session::put(sha1($link), $link);
        return response()->json(['type' => 'success', 'download_link' => route('transfer.download.all', $transfer->link)]);
    }

    public function downloadAll($link)
    {
        $transfer = Transfer::where([['link', $link], ['expiry_at', '>', Carbon::now()], ['status', 1]])
            ->orWhere([['link', $link], ['expiry_at', null], ['status', 1]])->with(['storageProvider', 'transferFiles'])->firstOrFail();
        abort_if(!Session::has(sha1($transfer->link)), 401);
        if (!is_null($transfer->password) && !Session::has($transfer->link . '_' . sha1($transfer->password))) {
            return redirect()->route('transfer.download.password', $transfer->link);
        }
        if ($transfer->storageProvider->symbol != "local") {
            toastr()->error(lang('Download all not supported on this transfer', 'download page'));
            return redirect()->route('transfer.download.index', $transfer->link);
        }
        $transferFiles = TransferFile::where('transfer_id', $transfer->id)->with('storageProvider')->get();
        if (is_null($transfer->downloaded_at)) {
            $transfer->update(['downloaded_at' => Carbon::now()]);
            if ($transfer->download_notify) {
                if (settings('mail_status')) {
                    $details = [
                        'subject' => str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', mailTemplates('Your transferred files has been downloaded {transfer_number}', 'transferred files downloaded notification')),
                        'transfer_number' => $transfer->unique_id,
                        'sender' => $transfer->sender_name ?? $transfer->sender_email,
                        'transfer_subject' => $transfer->subject ?? null,
                        'total_files' => $transfer->transferFiles->count(),
                        'total_size' => formatBytes($transfer->transferFiles->sum('size')),
                        'transfer_link' => $transfer->user_id ? route('user.transfers.show', $transfer->unique_id) : null,
                        'files' => $transfer->transferFiles,
                        'downloaded_at' => vDate($transfer->downloaded_at),
                        'expiry_at' => vDate($transfer->expiry_at),
                    ];
                    $this->notifyOnDownload($transfer->sender_email, $details);
                }
            }
        }
        if ($transferFiles->count() > 1) {
            $files = [];
            foreach ($transferFiles as $transferFile) {
                $transferFile->increment('downloads', 1);
                $files[storage_path('app/public/' . $transferFile->path)] = $transferFile->name;
            }
            Session::forget(sha1($transfer->link));
            $zipName = $transfer->link . '_' . time() . '.zip';
            return Zip::create($zipName, $files);
        } else {
            foreach ($transferFiles as $transferFile) {
                try {
                    $handler = $transferFile->storageProvider->handler;
                    $download = $handler::download($transferFile);
                    Session::forget(sha1($transfer->link));
                    $transferFile->increment('downloads', 1);
                    if ($transferFile->storageProvider->symbol != "local") {
                        return redirect($download);
                    } else {
                        return $download;
                    }
                } catch (Exception $e) {
                    toastr()->error(lang('There was a problem while trying to download the file', 'download page'));
                    return redirect()->route('transfer.download.index', $transfer->link);
                }
            }
        }
    }

    protected function notifyOnDownload($email, $details)
    {
        Mail::to($email)->send(new TransferDownloadedMail($details));
    }
}
