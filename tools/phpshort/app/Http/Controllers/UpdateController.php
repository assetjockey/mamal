<?php

namespace App\Http\Controllers;

use App\Models\Migration;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class UpdateController extends Controller
{
    /**
     * Show the Welcome page.
     */
    public function index(): View
    {
        return view('update.welcome');
    }

    /**
     * Show the Overview page.
     */
    public function overview(): View
    {
        $migrations = $this->getMigrations();
        $executedMigrations = $this->getExecutedMigrations();

        return view('update.overview', ['updates' => count($migrations) - count($executedMigrations)]);
    }

    /**
     * Show the Complete page.
     */
    public function complete(): View
    {
        return view('update.complete');
    }

    /**
     * Update the database with the new migrations.
     */
    public function updateDatabase(): RedirectResponse
    {
        $migrateDatabase = $this->migrateDatabase();
        if ($migrateDatabase !== true) {
            logger()->error($migrateDatabase);
            return back()->with('error', __('Failed to migrate the database. ' . $migrateDatabase));
        }

        return redirect()->route('update.complete');
    }

    /**
     * Migrate the database.
     */
    private function migrateDatabase(): bool|string
    {
        ini_set('max_execution_time', 1);

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the available migrations.
     */
    private function getMigrations(): array
    {
        $migrations = scandir(database_path() . '/migrations');

        $output = [];
        foreach ($migrations as $migration) {
            if ($migration != '.' && $migration != '..' && str_ends_with($migration, '.php')) {
                $output[] = str_replace('.php', '', $migration);
            }
        }

        return $output;
    }

    /**
     * Get the executed migrations.
     */
    private function getExecutedMigrations(): Collection
    {
        return Migration::all()->pluck('migration');
    }
}
