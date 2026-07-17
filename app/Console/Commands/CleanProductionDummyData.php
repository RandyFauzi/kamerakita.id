<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class CleanProductionDummyData extends Command
{
    protected $signature = 'production:clean-dummy-data
        {--keep-email=randyfauzi24@gmail.com : Main account email that must be preserved}
        {--password= : Optional new password for the preserved account}
        {--delete-evidence-files : Also delete uploaded evidence files in storage/app/public/evidences}
        {--yes : Skip the final confirmation prompt}
        {--force : Required to actually delete data}';

    protected $description = 'Clean demo operational data while preserving one main superadmin account.';

    public function handle(): int
    {
        $keepEmail = strtolower((string) $this->option('keep-email'));
        $newPassword = $this->option('password');
        $deleteEvidenceFiles = (bool) $this->option('delete-evidence-files');

        $this->warn('Production dummy data cleanup preview');
        $this->line('Preserved account: '.$keepEmail);
        $this->line('Environment: '.app()->environment());
        $this->line('Database: '.config('database.default'));
        $this->newLine();

        $before = $this->counts();
        $this->table(['Table', 'Rows before cleanup'], $this->formatCounts($before));

        if (! $this->option('force')) {
            $this->warn('Dry run only. Add --force to execute cleanup.');
            return self::SUCCESS;
        }

        if (! $this->option('yes') && ! $this->confirm('This will delete partners, reports, invoices, and all users except '.$keepEmail.'. Continue?')) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($keepEmail, $newPassword): void {
            $mainUser = User::query()->whereRaw('LOWER(email) = ?', [$keepEmail])->first();

            if (! $mainUser) {
                $mainUser = new User();
                $mainUser->name = 'Randy Fauzi';
                $mainUser->email = $keepEmail;
                $mainUser->password = Hash::make($newPassword ?: 'ChangeMeNow123!');
            } elseif ($newPassword) {
                $mainUser->password = Hash::make($newPassword);
            }

            $mainUser->role = 'superadmin';
            $mainUser->email_verified_at = $mainUser->email_verified_at ?: now();
            $mainUser->save();

            DB::table('video_work_reports')->delete();
            DB::table('client_invoices')->delete();
            DB::table('partners')->delete();
            DB::table('users')->where('id', '!=', $mainUser->id)->delete();
        });

        if ($deleteEvidenceFiles) {
            $evidencePath = storage_path('app/public/evidences');

            if (File::isDirectory($evidencePath)) {
                File::cleanDirectory($evidencePath);
                $this->info('Evidence files deleted: '.$evidencePath);
            }
        }

        $after = $this->counts();
        $this->newLine();
        $this->table(['Table', 'Rows after cleanup'], $this->formatCounts($after));
        $this->info('Cleanup complete. Main account is preserved as superadmin.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        return [
            'users' => DB::table('users')->count(),
            'partners' => DB::table('partners')->count(),
            'video_work_reports' => DB::table('video_work_reports')->count(),
            'client_invoices' => DB::table('client_invoices')->count(),
        ];
    }

    /**
     * @param array<string, int> $counts
     * @return array<int, array<int, string|int>>
     */
    private function formatCounts(array $counts): array
    {
        return collect($counts)
            ->map(fn (int $count, string $table): array => [$table, $count])
            ->values()
            ->all();
    }
}
