<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallConfigRequest;
use App\Http\Requests\InstallDatabaseRequest;
use App\Services\UserService;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstallController extends Controller
{
    /**
     * The user service instance.
     */
    public UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Show the Welcome page.
     */
    public function index(): View
    {
        return view('install.welcome');
    }

    /**
     * Show the Requirements page.
     */
    public function requirements(): View
    {
        $requirements = config('install.extensions');

        $results = [];
        foreach ($requirements as $type => $extensions) {
            if (strtolower($type) == 'php') {
                foreach ($requirements[$type] as $extension) {
                    $results['extensions'][$type][$extension] = true;

                    if (!extension_loaded($extension)) {
                        $results['extensions'][$type][$extension] = false;

                        $results['errors'] = true;
                    }
                }
            } elseif (strtolower($type) == 'apache') {
                foreach ($requirements[$type] as $extension) {
                    if (function_exists('apache_get_modules')) {
                        $results['extensions'][$type][$extension] = true;

                        if (!in_array($extension, apache_get_modules())) {
                            $results['extensions'][$type][$extension] = false;

                            $results['errors'] = true;
                        }
                    }
                }
            }
        }

        if (version_compare(PHP_VERSION, config('install.php_version'), '<')) {
            $results['errors'] = true;
        }

        return view('install.requirements', ['results' => $results]);
    }

    /**
     * Show the Permissions page.
     */
    public function permissions(): View
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

        return view('install.permissions', ['results' => $results]);
    }

    /**
     * Show the Database configuration page.
     */
    public function database(): View
    {
        return view('install.database');
    }

    /**
     * Show the Admin credentials page.
     */
    public function account(): View
    {
        return view('install.account');
    }

    /**
     * Show the Complete page.
     */
    public function complete(): View
    {
        return view('install.complete');
    }

    /**
     * Validate the database credentials, and write the .env config file.
     */
    public function storeConfig(InstallConfigRequest $request): RedirectResponse
    {
        $validateDatabase = $this->validateDatabaseCredentials($request);
        if ($validateDatabase !== true) {
            return back()->with('error', __('Invalid database credentials. ' . $validateDatabase))->withInput();
        }

        $validateConfigFile = $this->writeEnvFile($request);
        if ($validateConfigFile !== true) {
            return back()->with('error', __('Unable to save .env file, check file permissions. ' . $validateConfigFile))->withInput();
        }

        return redirect()->route('install.account');
    }

    /**
     * Migrate the database, and create the admin user.
     */
    public function storeDatabase(InstallDatabaseRequest $request): RedirectResponse
    {
        $migrateDatabase = $this->migrateDatabase();
        if ($migrateDatabase !== true) {
            return back()->with('error', __('Failed to migrate the database. ' . $migrateDatabase))->withInput();
        }

        $createDefaultUser = $this->createAdminUser($request);
        if ($createDefaultUser !== true) {
            return back()->with('error', __('Failed to create the default user. ' . $createDefaultUser))->withInput();
        }

        if ($request->input('newsletter')) {
            $this->subscribeNewsletter($request);
        }

        return redirect()->route('install.complete');
    }

    /**
     * Validate the database credentials.
     */
    private function validateDatabaseCredentials(Request $request): bool|string
    {
        $settings = config("database.connections.mysql");

        config([
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => array_merge($settings, [
                        'driver' => 'mysql',
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Migrate the database.
     */
    private function migrateDatabase(): bool|string
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Create the default admin user.
     */
    private function createAdminUser(Request $request): bool|string
    {
        try {
            $this->userService->store($request->validated() + ['role' => 1, 'tfa' => 0, 'mark_email_as_verified' => true]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * Write the .env file.
     */
    private function writeEnvFile(Request $request): bool|string
    {
        $config =
            "APP_NAME='".config('info.software.name')."'\n".
            "APP_ENV=production\n".
            "APP_KEY=base64:'".base64_encode(Str::random(32))."'\n".
            "APP_DEBUG=false\n".
            "APP_URL='".route('home')."'\n".
            "\n".
            "LOG_CHANNEL=stack\n".
            "\n".
            "DB_CONNECTION=mysql\n".
            "DB_HOST='".$request->input('database_hostname')."'\n".
            "DB_PORT=".$request->input('database_port')."\n".
            "DB_DATABASE='".$request->input('database_name')."'\n".
            "DB_USERNAME='".$request->input('database_username')."'\n".
            "DB_PASSWORD='".$request->input('database_password')."'\n".
            "\n".
            "BROADCAST_DRIVER=log\n".
            "CACHE_DRIVER=file\n".
            "QUEUE_CONNECTION=sync\n".
            "SESSION_DRIVER=file\n".
            "SESSION_LIFETIME=120\n".
            "\n".
            "REDIS_HOST=127.0.0.1\n".
            "REDIS_PASSWORD=null\n".
            "REDIS_PORT=6379\n".
            "\n".
            "MAIL_DRIVER=smtp\n".
            "MAIL_HOST=smtp.mailtrap.io\n".
            "MAIL_PORT=2525\n".
            "MAIL_USERNAME=null\n".
            "MAIL_PASSWORD=null\n".
            "MAIL_ENCRYPTION=null\n".
            "MAIL_FROM_ADDRESS=null\n".
            "MAIL_FROM_NAME=\"\${APP_NAME}\"\n".
            "\n".
            "AWS_ACCESS_KEY_ID=\n".
            "AWS_SECRET_ACCESS_KEY=\n".
            "AWS_DEFAULT_REGION=us-east-1\n".
            "AWS_BUCKET=\n".
            "\n".
            "PUSHER_APP_ID=\n".
            "PUSHER_APP_KEY=\n".
            "PUSHER_APP_SECRET=\n".
            "PUSHER_APP_CLUSTER=mt1\n".
            "\n".
            "MIX_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"\n".
            "MIX_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"";

        try {
            file_put_contents(base_path('.env'), $config);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * Subscribe to the newsletter.
     */
    private function subscribeNewsletter(Request $request): void
    {
        $httpClient = new GuzzleClient(['timeout' => 10, 'verify' => false]);

        try {
            $httpClient->request('POST', 'https://api.lunatio.com/newsletter', [
                'form_params' => [
                    'email' => $request->input('email'),
                    'name' => $request->input('name')
                ]
            ]);
        } catch (Exception) {}
    }
}
