<?php

namespace Tecnozt\Proteczt\Console\Commands;

use Illuminate\Console\Command;
use Tecnozt\Proteczt\ProtecztLicenseService;

class CheckLicenseCommand extends Command
{
    protected $signature   = 'proteczt:check {--fresh : Bypass cache and fetch fresh status}';
    protected $description = 'Check current license status from Proteczt server';

    public function handle(ProtecztLicenseService $service): int
    {
        $this->info('Checking Proteczt license status...');

        $status = $this->option('fresh')
            ? $service->refreshStatus()
            : $service->checkStatus();

        $this->newLine();

        if ($status['active']) {
            $this->info('✓ License is ACTIVE');
        } else {
            $this->error('✗ License is INACTIVE or EXPIRED');
        }

        if (!empty($status['data'])) {
            $this->renderLicenseData($status['data']);
        }

        // Indicate if response is from cache or fail-open
        if (!empty($status['fail_open'])) {
            $this->newLine();
            $this->warn('⚠  Response is FAIL-OPEN (server unreachable) — reason: ' . ($status['fail_reason'] ?? 'unknown'));
        } elseif (!$this->option('fresh')) {
            $this->newLine();
            $this->line('<fg=gray>ℹ  Status may be from cache. Run with --fresh to force update.</>');
        }

        $this->newLine();
        return $status['active'] ? Command::SUCCESS : Command::FAILURE;
    }

    protected function renderLicenseData(array $data): void
    {
        $this->newLine();
        $this->line('<fg=gray>License Information:</>');
        $this->line('  App:    ' . ($data['app_name'] ?? 'N/A'));
        $this->line('  Domain: ' . ($data['domain'] ?? 'N/A'));
        $this->line('  Status: ' . ($data['status'] === 'active' ? '<fg=green>Active</>' : '<fg=red>Inactive</>'));

        if (!empty($data['expired_at'])) {
            $expiredAt = \Carbon\Carbon::parse($data['expired_at']);
            $daysLeft  = (int) now()->diffInDays($expiredAt, false);

            if ($daysLeft > 0) {
                $color = $daysLeft <= 14 ? 'yellow' : 'green';
                $this->line("  Expires: <fg={$color}>{$expiredAt->format('d M Y')} ({$daysLeft} days left)</>");
            } else {
                $this->line("  Expires: <fg=red>{$expiredAt->format('d M Y')} (EXPIRED)</>");
            }
        } else {
            $this->line('  Expires: <fg=gray>No expiration</>');
        }
    }
}
