<?php

namespace App\Console\Commands;

use App\Services\PartnerActivityStatusService;
use Illuminate\Console\Command;

class SyncPartnerActivityStatus extends Command
{
    protected $signature = 'partners:sync-activity-status';

    protected $description = 'Sync partner active/inactive status based on recent work report submissions.';

    public function handle(PartnerActivityStatusService $service): int
    {
        $result = $service->syncAll();

        $this->info('Partner activity status sync complete.');
        $this->line('Checked: '.$result['checked']);
        $this->line('Activated: '.$result['activated']);
        $this->line('Inactivated: '.$result['inactivated']);

        return self::SUCCESS;
    }
}
