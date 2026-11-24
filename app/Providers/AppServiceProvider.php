<?php

namespace App\Providers;

use App\Models\McpOAuthAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::useTokenModel(McpOAuthAccessToken::class);
        Passport::tokensCan([
            'mcp:use' => 'Akses Kamerakita MCP Server',
        ]);
        Passport::authorizationView(fn (array $parameters) => view('admin.integrations.mcp-authorize', $parameters));

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event) {
                if (\App\Services\AccountPovService::isSuppressingAuthEvents()) {
                    return;
                }

                \App\Services\ActivityLogger::log('auth.login', "Pengguna {$event->user->name} ({$event->user->email}) berhasil login.");
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function (\Illuminate\Auth\Events\Logout $event) {
                if (\App\Services\AccountPovService::isSuppressingAuthEvents()) {
                    return;
                }

                if ($event->user) {
                    \App\Services\ActivityLogger::log('auth.logout', "Pengguna {$event->user->name} ({$event->user->email}) telah logout.");
                }
            }
        );
    }
}
