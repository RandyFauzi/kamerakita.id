<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAtlasData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atlas:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis data task dari Atlas Dashboard setiap 3 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi data dari Atlas...');

        try {
            // 1. Login ke Atlas untuk mendapatkan Bearer Token / Cookie
            $loginResponse = \Illuminate\Support\Facades\Http::post('https://atlasdashboard.vtlabs.dev/api/login', [
                'email' => 'nandakoso88@gmail.com',
                'password' => 'nandakoso88@gmail.com'
            ]);

            if (!$loginResponse->successful()) {
                $this->error('Gagal login ke Atlas. Status: ' . $loginResponse->status());
                return;
            }

            // Asumsi response login mengembalikan token
            $token = $loginResponse->json('token') ?? $loginResponse->json('access_token');
            
            if (!$token) {
                $this->error('Token tidak ditemukan dalam response login.');
                return;
            }

            // 2. Mengambil data tasks (Semua log/history)
            // Endpoint ini asumsi, bisa disesuaikan nanti dengan endpoint asli Atlas
            $tasksResponse = \Illuminate\Support\Facades\Http::withToken($token)
                ->get('https://atlasdashboard.vtlabs.dev/api/tasks/history'); 

            if (!$tasksResponse->successful()) {
                $this->error('Gagal menarik data tugas dari Atlas.');
                return;
            }

            $tasks = $tasksResponse->json('data') ?? $tasksResponse->json();

            $count = 0;
            if (is_array($tasks)) {
                // 3. Looping data dan Insert/Update ke database kita
                foreach ($tasks as $task) {
                    \App\Models\AtlasTask::updateOrCreate(
                        [
                            'email' => $task['worker_email'] ?? $task['email'] ?? 'nandakoso88@gmail.com', // identifier (user email)
                            'task_date' => $task['date'] ?? date('Y-m-d'),
                            'task_name' => $task['task_name'] ?? 'Daily Task',
                        ],
                        [
                            'time_str' => $task['time_str'] ?? null,
                            'approved_mins' => $task['approved_mins'] ?? 0,
                            'rejected_mins' => $task['rejected_mins'] ?? 0,
                            'pending_mins' => $task['pending_mins'] ?? 0,
                            'total_video_mins' => $task['total_video_mins'] ?? 0,
                            'notes' => $task['notes'] ?? null,
                        ]
                    );
                    $count++;
                }
            }

            $this->info("Berhasil melakukan sinkronisasi $count data task Atlas.");
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
