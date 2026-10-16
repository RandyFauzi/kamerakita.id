<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAtlasData extends Command
{
    protected $signature = 'atlas:sync';

    protected $description = 'Legacy Atlas sync entry point (disabled; use the versioned Playwright bot)';

    public function handle(): int
    {
        $this->error('Legacy HTTP scraper disabled: it uses the old Atlas schema. Run scripts/atlas_sync.py from the bot host instead.');

        return self::FAILURE;
    }
}
