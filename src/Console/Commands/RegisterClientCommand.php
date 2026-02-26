<?php

namespace Tecnozt\Proteczt\Console\Commands;

use Illuminate\Console\Command;
use Tecnozt\Proteczt\ProtecztLicenseService;

class RegisterClientCommand extends Command
{
    protected $signature   = 'proteczt:register {--force : Force re-register even if already registered}';
    protected $description = 'Register this application to the Proteczt license server';

    public function handle(ProtecztLicenseService $service): int
    {
        $markerFile = storage_path('app/.proteczt_registered');

        if (file_exists($markerFile) && !$this->option('force')) {
            $info = json_decode(file_get_contents($markerFile), true);
            $this->warn('This application is already registered.');
            $this->line('  Registered at: ' . ($info['registered_at'] ?? 'unknown'));
            $this->line('  Domain:        ' . ($info['domain'] ?? 'unknown'));
            $this->newLine();
            $this->line('Use --force to re-register.');
            return Command::SUCCESS;
        }

        $this->info('Registering application to Proteczt...');

        $registered = $service->register();

        $this->newLine();

        if ($registered) {
            @file_put_contents($markerFile, json_encode([
                'registered_at' => now()->toIso8601String(),
                'domain'        => config('proteczt.domain') ?? request()->getHost(),
            ]));

            $this->info('✓ Application registered successfully.');
        } else {
            $this->error('✗ Registration failed. Check PROTECZT_API_URL and PROTECZT_API_TOKEN.');
            return Command::FAILURE;
        }

        $this->newLine();
        return Command::SUCCESS;
    }
}
