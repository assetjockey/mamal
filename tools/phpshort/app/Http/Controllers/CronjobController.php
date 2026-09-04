<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessCronjobRequest;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class CronjobController extends Controller
{
    /**
     * Run the scheduled cron job commands.
     */
    public function index(ProcessCronjobRequest $request): JsonResponse
    {
        ini_set('max_execution_time', 0);

        Artisan::call('schedule:run');

        Setting::where('name', 'cronjob_executed_at')->update(['value' => Carbon::now()->timestamp]);

        return response()->json([
            'status' => 200
        ], 200);
    }
}
