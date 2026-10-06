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
        $this->info('Memulai sinkronisasi data dari Atlas di Server...');

        try {
            // 1. Buat instance HTTP Client dengan cookie jar untuk menyimpan sesi login
            $client = new \GuzzleHttp\Client(['cookies' => true, 'verify' => false]);
            
            $this->info('Mencoba akses halaman login Atlas...');
            
            // Step A: Akses halaman login (mungkin untuk mengambil CSRF token atau Inisiasi Session)
            $loginPage = $client->get('https://atlasdashboard.vtlabs.dev/login');
            $html = (string) $loginPage->getBody();
            
            // Coba ambil CSRF token dari meta tag atau input hidden (jika ada)
            $csrfToken = '';
            if (preg_match('/<meta name="csrf-token" content="(.*?)"/', $html, $matches)) {
                $csrfToken = $matches[1];
            } elseif (preg_match('/<input type="hidden" name="_token" value="(.*?)"/', $html, $matches)) {
                $csrfToken = $matches[1];
            }

            $this->info('Mengirim data login...');
            
            // Step B: Submit Login Form
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Referer' => 'https://atlasdashboard.vtlabs.dev/login',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            ];
            
            $loginPayload = [
                'email' => 'nandakoso88@gmail.com',
                'password' => 'nandakoso88@gmail.com'
            ];
            if ($csrfToken) {
                $loginPayload['_token'] = $csrfToken;
            }

            $responseLogin = $client->post('https://atlasdashboard.vtlabs.dev/login', [
                'headers' => $headers,
                'form_params' => $loginPayload,
                'allow_redirects' => true
            ]);

            // Cek apakah login berhasil (redirect ke dashboard / recordings)
            $this->info('Login dieksekusi. Memuat halaman recordings...');

            // Step C: Akses Halaman Recordings
            $recordingsPage = $client->get('https://atlasdashboard.vtlabs.dev/recordings', [
                'headers' => $headers
            ]);
            
            $recordingsHtml = (string) $recordingsPage->getBody();

            // Pengecekan sederhana apakah ada tabel
            if (!str_contains($recordingsHtml, '<table')) {
                $this->error('Tabel data tidak ditemukan di halaman. Kemungkinan sistem Atlas menggunakan Javascript/React (SPA) untuk memuat data, atau login ditolak.');
                return;
            }

            // Parse HTML menggunakan DOMDocument
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($recordingsHtml);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            // Mencari baris tr di dalam tbody
            $rows = $xpath->query('//table//tbody//tr');

            if ($rows->length === 0) {
                $this->error('Tidak ada baris data (tr) yang ditemukan di dalam tabel. Kemungkinan data dirender via JSON API.');
                return;
            }

            $count = 0;
            foreach ($rows as $row) {
                $cols = $xpath->query('td', $row);
                if ($cols->length < 8) continue;
                
                $taskName = trim($cols->item(0)->textContent);
                $participant = trim($cols->item(1)->textContent);
                $recordedUtc = trim($cols->item(2)->textContent);
                $durationSec = floatval(trim($cols->item(3)->textContent));
                $status = trim($cols->item(4)->textContent);
                $billableStatus = trim($cols->item(5)->textContent);
                $hourRaw = trim($cols->item(6)->textContent);
                $reviewedUtc = trim($cols->item(7)->textContent);
                $reconciliation = $cols->length > 8 ? trim($cols->item(8)->textContent) : '';

                // Ekstrak breakdown
                $billableHours = 0; $rejectedHours = 0; $pendingHours = 0;
                if (preg_match('/Billable\s+([\d.]+)\s*h/i', $hourRaw, $m)) $billableHours = floatval($m[1]);
                if (preg_match('/Rejected\s+([\d.]+)\s*h/i', $hourRaw, $m)) $rejectedHours = floatval($m[1]);
                if (preg_match('/Pending\s+([\d.]+)\s*h/i', $hourRaw, $m)) $pendingHours = floatval($m[1]);

                \App\Models\AtlasTask::updateOrCreate(
                    [
                        'email' => $participant,
                        'task_date' => date('Y-m-d', strtotime($recordedUtc)),
                        'task_name' => $taskName,
                    ],
                    [
                        'time_str' => date('H:i:s', strtotime($recordedUtc)),
                        'approved_mins' => $billableHours * 60,
                        'rejected_mins' => $rejectedHours * 60,
                        'pending_mins' => $pendingHours * 60,
                        'total_video_mins' => $durationSec / 60,
                        'notes' => $reconciliation,
                    ]
                );
                $count++;
            }

            $this->info("Berhasil melakukan sinkronisasi $count data task Atlas langsung dari server.");
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
