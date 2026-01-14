<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        //
        FilamentView::registerRenderHook(
            'panels::auth.login.form.after',
            fn(): string => Blade::render('@vite(\'resources/css/custom-login.css\')'),
        );
        if ($this->app->environment('production') || env('APP_URL') == 'https://superfood.sat-sistemas.uk') {
            URL::forceScheme('https');

            // Esto arregla el problema de "Mixed Content" con el Load Balancer de Cloudflare
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
