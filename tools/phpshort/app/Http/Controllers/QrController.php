<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Show the QR code.
     */
    public function show(Request $request): Response
    {
        if (parse_url($request->input('url'), PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST) && !Domain::where('name', parse_url($request->input('url'), PHP_URL_HOST))->exists()) {
            abort(404);
        }

        $qr = QrCode::format('svg')
            ->size(500)
            ->margin(2)
            ->errorCorrection('L')
            ->generate($request->input('url'));

        return response($qr)->header('Content-Type', 'image/svg+xml');
    }
}
