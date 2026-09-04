<?php

use App\Console\Commands\CheckMonitorsCommand;
use App\Console\Commands\CheckMonitorsDomainCommand;
use App\Console\Commands\CheckMonitorsSslCommand;
use App\Console\Commands\DeleteExpiredDataCommand;
use App\Console\Commands\DeleteUnverifiedUsersCommand;
use App\Console\Commands\PauseMonitorsPlanLimitsCommand;
use App\Console\Commands\PauseOfflineMonitorsCommand;
use App\Console\Commands\PauseOnlineMonitorsInactiveUsersCommand;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Console\ViewClearCommand;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware groups
        $middleware->web([
            SetLocale::class
        ]);

        $middleware->api([
            SetLocale::class
        ]);

        // Middleware settings
        $middleware->authenticateSessions();

        $middleware->redirectGuestsTo(function (): string {
            return route('login');
        });

        $middleware->redirectUsersTo(function (): string {
            return route('home');
        });

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'login/apple',
        ]);

        $middleware->trimStrings(except: [
            'password',
            'password_confirmation',
            'current_password',
            'request_auth_password',
        ]);

        $middleware->encryptCookies(except: [
            'dark_mode',
            'cookie_law',
            'announcement_guest_id',
            'announcement_user_id'
        ]);

        $middleware->trustProxies(
            at: '*',
            headers:
                SymfonyRequest::HEADER_X_FORWARDED_FOR |
                SymfonyRequest::HEADER_X_FORWARDED_HOST |
                SymfonyRequest::HEADER_X_FORWARDED_PORT |
                SymfonyRequest::HEADER_X_FORWARDED_PROTO |
                SymfonyRequest::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'request_auth_password',
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(ClearCommand::class)->weeklyOn(0, '01:00');
        $schedule->command(ViewClearCommand::class)->weeklyOn(0, '02:00');
        $schedule->command(ClearResetsCommand::class)->weeklyOn(0, '03:00');
        $schedule->command(DeleteUnverifiedUsersCommand::class)->dailyAt('04:00');
        $schedule->command(DeleteExpiredDataCommand::class)->dailyAt('02:00');
        $schedule->command(CheckMonitorsCommand::class)->everyMinute();
        $schedule->command(PauseOfflineMonitorsCommand::class)->dailyAt('02:00');
        $schedule->command(PauseOnlineMonitorsInactiveUsersCommand::class)->dailyAt('03:00');
        $schedule->command(PauseMonitorsPlanLimitsCommand::class)->dailyAt('04:00');
        $schedule->command(CheckMonitorsSslCommand::class)->dailyAt('05:00');
        $schedule->command(CheckMonitorsDomainCommand::class)->dailyAt('06:00');
    })
    ->create();
