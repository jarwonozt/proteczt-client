<?php

namespace Tecnozt\Proteczt;

use Illuminate\Support\ServiceProvider;
use Tecnozt\Proteczt\Console\Commands\CheckLicenseCommand;
use Tecnozt\Proteczt\Console\Commands\RefreshLicenseCommand;
use Tecnozt\Proteczt\Console\Commands\RegisterClientCommand;

class ProtecztServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/proteczt.php',
            'proteczt'
        );

        // Register the main service as a singleton
        $this->app->singleton(ProtecztLicenseService::class, function ($app) {
            return new ProtecztLicenseService(
                $app['config']['proteczt'],
                $app['cache.store'],
                $app['log']
            );
        });

        // Register alias for facade
        $this->app->alias(ProtecztLicenseService::class, 'proteczt');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'proteczt');

        // Publish config file
        $this->publishes([
            __DIR__ . '/../config/proteczt.php' => config_path('proteczt.php'),
        ], 'proteczt-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/proteczt'),
        ], 'proteczt-views');

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckLicenseCommand::class,
                RefreshLicenseCommand::class,
                RegisterClientCommand::class,
            ]);
        }

        // Auto-register client on first boot
        if (config('proteczt.auto_register', true) && !app()->runningInConsole()) {
            $this->autoRegisterClient();
        }
    }

    /**
     * Auto-register the client application to Proteczt server on first boot.
     */
    protected function autoRegisterClient(): void
    {
        try {
            $markerFile = storage_path('app/.proteczt_registered');

            if (!file_exists($markerFile)) {
                /** @var ProtecztLicenseService $service */
                $service = $this->app->make(ProtecztLicenseService::class);
                $registered = $service->register();

                if ($registered) {
                    // Store registration marker with timestamp
                    @file_put_contents($markerFile, json_encode([
                        'registered_at' => now()->toIso8601String(),
                        'domain' => config('proteczt.domain') ?? request()->getHost(),
                    ]));
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — never break the host application
        }
    }
}
