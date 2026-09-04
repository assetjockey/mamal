<?php

namespace App\Console\Commands;

use App\Mail\TransferExpiredMail;
use App\Models\Transfer;
use App\Models\TransferFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DeleteExpiredTransferFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:expired-files-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired transfer files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $transfers = Transfer::where([['expiry_at', '<', Carbon::now()], ['status', 1], ['files_deleted_at', null]])->get();
        foreach ($transfers as $transfer) {
            $transferFiles = TransferFile::where('transfer_id', $transfer->id)->get();
            if ($transfer->expiry_notify) {
                $transferDetails = [
                    'subject' => str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', mailTemplates('Your transferred has been expired {transfer_number}', 'transfer expired notification')),
                    'transfer_number' => $transfer->unique_id,
                    'sender' => $transfer->sender_name ?? $transfer->sender_email,
                    'transfer_subject' => $transfer->subject ?? null,
                    'total_files' => $transferFiles->count(),
                    'total_size' => formatBytes($transferFiles->sum('size')),
                    'transfer_link' => $transfer->user_id ? route('user.transfers.show', $transfer->unique_id) : null,
                    'files' => $transferFiles,
                    'downloaded_at' => $transfer->downloaded_at ? vDate($transfer->downloaded_at) : null,
                ];
            }
            foreach ($transferFiles as $transferFile) {
                $handler = $transferFile->storageProvider->handler;
                if ($handler::delete($transferFile->path)) {
                    $transferFile->delete();
                }
            }
            $updateDeleteDate = $transfer->update(['files_deleted_at' => Carbon::now()]);
            if ($updateDeleteDate) {
                if (!is_null($transfer->user_id)) {
                    $title = str_replace('{transfer_number}', '[#' . $transfer->unique_id . ']', lang('Transfer {transfer_number} has expired', 'notifications'));
                    $image = asset('images/icons/transfer-expired.png');
                    $link = route('user.transfers.show', $transfer->unique_id);
                    userNotify($transfer->user_id, $title, $image, $link);
                }
            }
            if ($transfer->expiry_notify) {
                if (settings('mail_status')) {
                    Mail::to($transfer->sender_email)->send(new TransferExpiredMail($transferDetails));
                }
            }
        }
    }
}
