<?php

namespace Vironeer\License;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Vironeer\License\App\Http\Middleware\InstalledMiddleware;
use Vironeer\License\App\Http\Middleware\NotInstalledMiddleware;

class VironeerLicenseServiceProvider extends ServiceProvider
{
    protected $namespace = 'Vironeer\License\App\Http\Controllers';

    public function boot()
    {
        $this->registerHelpers();
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('installed', InstalledMiddleware::class);
        $router->aliasMiddleware('notInstalled', NotInstalledMiddleware::class);

        Route::group(['namespace' => $this->namespace], function () {
            $this->loadRoutesFrom(__DIR__ . '/Routes.php');
        });

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'vironeer');

    }
    public function registerHelpers()
    {
        if (file_exists($file = __DIR__ . '/Helper.php')) {
            require $file;
        }
    }
}
