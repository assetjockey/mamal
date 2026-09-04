<?php

namespace Modules\AppQRPrePrinted\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrePrintedExportController
{
    public function __invoke(Request $request): StreamedResponse
    {
        $batch = trim((string) $request->query('batch', ''));
        abort_if($batch === '', Response::HTTP_NOT_FOUND);

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId($request->user());
        $qrCodes = AppQrCode::query()
            ->where('owner_user_id', $ownerId)
            ->where('settings->source', 'pre_printed')
            ->where(function ($query) use ($batch): void {
                $query->where('settings->pre_printed->batch_id', $batch)
                    ->orWhere('settings->pre_printed->batch_name', $batch);
            })
            ->orderBy('id')
            ->get();

        abort_if($qrCodes->isEmpty(), Response::HTTP_NOT_FOUND);

        $batchName = (string) data_get($qrCodes->first()->settings, 'pre_printed.batch_name', $batch);
        $filename = (Str::slug($batchName) ?: 'pre-printed-qr-batch').'.csv';

        return response()->streamDownload(function () use ($qrCodes): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'name',
                'short_code',
                'short_url',
                'status',
                'type',
                'destination_url',
                'qr_style_template',
                'public_template',
                'created_at',
            ]);

            foreach ($qrCodes as $qrCode) {
                fputcsv($handle, [
                    $qrCode->name,
                    $qrCode->short_code,
                    $qrCode->shortUrl(),
                    $qrCode->status,
                    $qrCode->type,
                    $qrCode->destination_url,
                    data_get($qrCode->settings, 'pre_printed.qr_style_template', data_get($qrCode->settings, 'style_template')),
                    data_get($qrCode->settings, 'pre_printed.public_template', data_get($qrCode->settings, 'public_template')),
                    optional($qrCode->created_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
