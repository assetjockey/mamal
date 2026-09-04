<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{
    /**
     * Run the database migrations and seeders, then return to the dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDatabase()
    {
        $result = $this->migrateDatabase();

        if ($result !== true) {
            toaster()->error(__('Failed to update the database: ') . $result);

            return back();
        }

        toaster()->success(__('Database updated successfully'));

        return redirect()->route('admin.dashboard');
    }

    /**
     * Migrate the database and (re)seed idempotent reference data.
     *
     * @return bool|string  true on success, otherwise the error message
     */
    private function migrateDatabase()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            return true;
        } catch (\Throwable $e) {
            Log::error('Database update failed', ['exception' => $e]);

            return $e->getMessage();
        }
    }
}
