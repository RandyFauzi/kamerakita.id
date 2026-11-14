<?php

namespace App\Console\Commands;

use App\Models\RoboticsSyncRun;
use App\Services\Robotics1618Sync;
use Illuminate\Console\Command;

class SyncRobotics1618 extends Command
{
    protected $signature = 'robotics1618:sync {--request : Enqueue scheduled run} {--work : Process pending request} {--status : Show server status without contacting 1618}';

    protected $description = 'Synchronize official 1618 CSV using the server, without a local computer';

    public function handle(Robotics1618Sync $sync): int
    {
        if ($this->option('status')) {
            $run = RoboticsSyncRun::latest('id')->first();
            $this->line(json_encode(['enabled' => (bool) config('services.robotics1618.enabled'),
                'last_status' => $run?->status, 'result' => $run?->result,
                'finished_at' => $run?->finished_at?->toIso8601String()], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }
        if (! $this->option('work')) {
            $sync->enqueue($this->option('request') ? 'scheduled' : 'cli');
        }
        if ($this->option('request')) {
            $this->info('Permintaan pembaruan 1618 disimpan.');

            return self::SUCCESS;
        }
        $run = $sync->work();
        $this->line($run?->message ?? 'Tidak ada pekerjaan / proses lain masih berjalan.');

        return $run?->status === 'failed' ? self::FAILURE : self::SUCCESS;
    }
}
