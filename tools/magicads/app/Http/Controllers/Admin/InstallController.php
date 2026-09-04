<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\GeneralSetting;

class InstallController extends Controller
{
    protected $api;

    public function __construct()
    {
        $this->api = new LicenseController();
    }


    /**
     * Display install index page
     *
     */
    public function index()
    {
        return view('install.index');
    }


    /**
     * Check if hosting platform meets requirements 
     *
     */
    public function requirements()
    {
        $requirements = config('install.extensions');

        $results = [];
        // Check the requirements
        foreach ($requirements as $type => $extensions) {
            if (strtolower($type) == 'php') {
                foreach ($requirements[$type] as $extensions) {
                    $results['extensions'][$type][$extensions] = true;

                    if (! extension_loaded($extensions)) {
                        $results['extensions'][$type][$extensions] = false;

                        $results['errors'] = true;
                    }
                }
            } elseif (strtolower($type) == 'apache') {
                foreach ($requirements[$type] as $extensions) {
                    // Check if the function exists
                    // Prevents from returning a false error
                    if (function_exists('apache_get_modules')) {
                        $results['extensions'][$type][$extensions] = true;

                        if (! in_array($extensions, apache_get_modules())) {
                            $results['extensions'][$type][$extensions] = false;

                            $results['errors'] = true;
                        }
                    }
                }
            } elseif (strtolower($type) == 'functions') {
                // PHP functions can exist but be turned off via php.ini "disable_functions".
                // These are SOFT requirements: their status is reported, but a disabled
                // function never blocks installation (the user is notified to enable them
                // on their hosting elsewhere, outside the installer).
                $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

                foreach ($requirements[$type] as $function) {
                    $results['extensions'][$type][$function] = function_exists($function)
                        && ! in_array($function, $disabled, true);
                }
            }
        }

        // If the current php version doesn't meet the requirements
        if (version_compare(PHP_VERSION, config('install.php_version')) == -1) {
            $results['errors'] = true;
        }

        return view('install.requirements', compact('results'));
    }


    /**
     * Check if hosting platform has proper permissions for select folders/paths
     *
     */
    public function permissions()
    {
        $permissions = config('install.permissions');

        $results = [];
        foreach ($permissions as $type => $files) {
            foreach ($files as $file) {
                if (is_writable(base_path($file))) {
                    $results['permissions'][$type][$file] = true;
                } else {
                    $results['permissions'][$type][$file] = false;
                    $results['errors'] = true;
                }
            }
        }

        return view('install.permissions', compact('results'));
    }


    /**
     * Display database inputs
     *
     */
    public function database()
    {
        return view('install.database');
    }


    /**
     * Process activation feature
     *
     */
    public function activation()
    {
        $processDatabase = $this->processDatabase();

        // processDatabase() returns a redirect response only when migration/seeding failed.
        // Bail out to that error response instead of advancing the wizard.
        if ($processDatabase !== null) {
            return $processDatabase;
        }

        $this->storeConfiguration('SESSION_DRIVER', 'database');

        return view('install.activation');
    }


    /**
     * Validate the database credentials, and write the .env config file
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDatabaseCredentials(Request $request)
    {
       
        request()->validate([
            'hostname' => 'required',
            'port' => 'required',
            'database' => 'required',
            'user' => 'required',
        ]);

        try {
            $validateDatabase = $this->validateDatabaseCredentials();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Provided database user credentials do not have access to this database. ' . $e);
        }
        
        if ($validateDatabase !== true) {
            return redirect()->back()->with('error', __('Invalid database credentials. ' . $validateDatabase));
        }
        
        if ($validateDatabase == true) {
            $this->storeConfiguration('DB_HOST', request('hostname'));
            $this->storeConfiguration('DB_PORT', request('port'));
            $this->storeConfiguration('DB_DATABASE', request('database'));
            $this->storeConfiguration('DB_USERNAME', request('user')); 
            
            $password = "'". request('password') . "'";
            $this->storeWithQuotes('DB_PASSWORD', $password);
        }
        
        return redirect()->route('install.activation');
    }


    /**
     * Activate user license
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function activateApplication(Request $request)
    {
        request()->validate([
            'license' => 'required|string',
            'username' => 'required|string',
        ]);

        $status = $this->api->activate_license(request('license'), request('username'));

        if ($status['status'] == true) {

            $createDefaultAdmin = $this->createDefaultAdmin();
            if ($createDefaultAdmin !== true) {
                return redirect()->back()->with('error', __('Failed to create the default admin. ' . $createDefaultAdmin));
            }

            $saveInstalledFile = $this->saveInstalledFile();
            if ($saveInstalledFile !== true) {
                return redirect()->back()->with('error', __('Failed to finalize the installation. ' . $saveInstalledFile));
            }

            $settings = GeneralSetting::first() ?: new GeneralSetting();
            $settings->license = $request->input('license');
            $settings->username = $request->input('username');
            $settings->license_type = $status['data'] ?? null;
            $settings->save();

        } else {
            return redirect()->back()->with('error', 'There was an error while activating your application, please contact support team.');
        }

        $activated = $status['status'];

        return view('install.complete', compact('activated', 'createDefaultAdmin'));      
    }


    /**
     * Process database creation
     *
     * @return \Illuminate\Http\RedirectResponse|null  Redirect on failure, null on success
     */
    public function processDatabase()
    {
        try {
            $migrateDatabase = $this->migrateDatabase();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during migrating your database, please contact support or try installation again. ' . $e->getMessage());
        }

        if ($migrateDatabase !== true) {
            return back()->with('error', __('Failed to migrate the database. ' . $migrateDatabase));
        }

        $seedDatabase = $this->seedDatabase();
        if ($seedDatabase !== true) {
            return back()->with('error', __('Failed to seed the database. ' . $seedDatabase));
        }

        return null;
    }


    /**
     * Migrate the database
     *
     * @return bool|string  true on success, error message on failure
     */
    private function migrateDatabase()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error($e);

            return $e->getMessage();
        }
    }


    /**
     * Seed the database
     *
     * @return bool|string  true on success, error message on failure
     */
    private function seedDatabase()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error($e);

            return $e->getMessage();
        }
    }


    /**
     * Create the default admin user
     *
     * @return bool|string  true on success, error message on failure
     */
    private function createDefaultAdmin()
    {
        try {
            $user = new User;
            $user_id = $this->generateUserId();

            $user->name = 'Admin';
            $user->user_id = $user_id;
            $user->email = 'admin@example.com';
            $user->password = Hash::make('admin12345');
            $user->status = 'active';
            $user->group = 'admin';
            $user->email_verified_at = now();
            $user->referral_id = strtoupper(Str::random(15));
            $user->credits = 1000000;
            $user->credits_prepaid = 0;
            $user->onboarding_completed = true;
            $user->onboarding_completed_at = now();
            $user->save();

            $user->assignRole('admin');

        } catch (\Exception $e) {
            Log::error($e);

            return $e->getMessage();
        }

        return true;
    }


    /**
     * Validate the database credentials
     *
     * @return bool|string  true on success, error message on failure
     */
    private function validateDatabaseCredentials()
    {
        $hostname = request('hostname');
        $database = request('database');
        $username = request('user');
        $password = request('password');

        // Create connection
        try {
            $conn = @mysqli_connect($hostname, $username, $password, $database);

            if (! $conn) {
                return mysqli_connect_error() ?: __('Unable to connect to the database with the provided credentials.');
            }

            mysqli_close($conn);

            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Record in .env file
     */
    private function storeConfiguration($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            try {
                file_put_contents($path, str_replace(
                    $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
                ));
            } catch (\Exception $e) {
                return back()->with('error', 'PHP file_put_contents() function is disabled in your hosting, enable it first');
            }
        }
    }

    private function storeWithQuotes($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            try {
                file_put_contents($path, str_replace(
                    $key . '=' . '\'' . env($key) . '\'', $key . '=' . $value, file_get_contents($path)
                ));
            } catch (\Exception $e) {
                return back()->with('error', 'PHP file_put_contents() function is disabled in your hosting, enable it first');
            }
        }
    }


    /**
     * Write the installed file
     *
     * @return bool|string
     */
    private function saveInstalledFile()
    {
        if (!file_exists(storage_path('installed'))) {
            try {
                file_put_contents(storage_path('installed'), '');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        return true;
    }

    private function generateUserId()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        // Generate first segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate second segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $code .= '-';
        
        // Generate third segment (5 chars)
        for ($i = 0; $i < 5; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return strtolower($code);
    }
}
