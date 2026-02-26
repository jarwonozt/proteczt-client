<?php

namespace Tecnozt\Proteczt\Console\Commands;

use Illuminate\Console\Command;
use Tecnozt\Proteczt\ProtecztLicenseService;

class RefreshLicenseCommand extends Command
{
    protected $signature   = 'proteczt:refresh';
    protected $description = 'Force refresh the license status cache from Proteczt server';

    public function handle(ProtecztLicenseService $service): int
    {
        $this->info('Refreshing license from Proteczt server...');

        $status = $service->refreshStatus();

        $this->newLine();

        if ($status['active']) {
            $this->info('✓ License is ACTIVE — cache updated.');
        } else {
            $this->error('✗ License is INACTIVE or EXPIRED.');
        }

        if (!empty($status['fail_open'])) {
            $this->warn('⚠  Server was unreachable. Cache was not updated (fail-open).');
        }

        $this->newLine();
        return $status['active'] ? Command::SUCCESS : Command::FAILURE;
    }
}
