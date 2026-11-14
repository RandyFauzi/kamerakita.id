<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Robotics1618Source
{
    private const BASE = 'https://partners.1618robotics.com';

    private CookieJar $cookies;

    private bool $loggedIn = false;

    private string $root;

    private float $deadline;

    public function __construct()
    {
        $tenant = config('services.robotics1618.tenant', 'kamerakita-ai');
        if (! preg_match('/^[a-z0-9-]+$/D', $tenant)) {
            throw new RuntimeException('Tenant 1618 tidak valid.');
        }
        $this->root = self::BASE.'/'.$tenant.'/';
        $this->cookies = new CookieJar;
        $this->deadline = microtime(true) + 480;
    }

    private function request(string $url, ?array $form = null)
    {
        $this->checkDeadline();
        $request = Http::withOptions(['cookies' => $this->cookies, 'allow_redirects' => false, 'stream' => true, 'read_timeout' => 45])
            ->withHeaders(['Accept' => 'application/json', 'Referer' => $this->root])
            ->connectTimeout(10)->timeout(45);
        try {
            $response = $form === null ? $request->get($url) : $request->asForm()->post($url, $form);
        } catch (\Throwable) {
            throw new RuntimeException('Koneksi 1618 gagal atau timeout. Coba lagi nanti.');
        }
        if ($response->redirect() || $response->status() === 401) {
            if ($form !== null) {
                $location = $response->header('Location');
                $target = str_starts_with($location, '/') && ! str_starts_with($location, '//') ? self::BASE.$location : $location;
                if (str_starts_with($target, self::BASE.'/') && parse_url($target, PHP_URL_PATH) !== '/login') {
                    return $response;
                }
                throw new RuntimeException('Login 1618 ditolak. Periksa konfigurasi kredensial.');
            }
            if ($this->loggedIn) {
                throw new RuntimeException('Sesi 1618 ditolak.');
            }
            $response->toPsrResponse()->getBody()->close();
            $this->loggedIn = true;
            $username = config('services.robotics1618.username');
            $password = config('services.robotics1618.password');
            if (! $username || ! $password) {
                throw new RuntimeException('Kredensial 1618 belum dikonfigurasi di server.');
            }
            $this->request(self::BASE.'/login', ['username' => $username, 'password' => $password, 'next' => parse_url($this->root, PHP_URL_PATH)]);

            return $this->request($url);
        }
        if (! $response->successful()) {
            throw new RuntimeException('1618 mengembalikan HTTP '.$response->status().'. Coba lagi nanti.');
        }

        return $response;
    }

    public function checkDeadline(): void
    {
        if (microtime(true) > $this->deadline) {
            throw new RuntimeException('Batas waktu sinkronisasi tercapai. Batch selesai tetap tersimpan dan aman dicoba ulang.');
        }
    }

    public function metadata(): array
    {
        $stream = $this->request($this->root.'data.json')->toPsrResponse()->getBody();
        $body = '';
        try {
            while (! $stream->eof()) {
                $this->checkDeadline();
                $body .= $stream->read(65536);
                if (strlen($body) > 8 * 1024 * 1024) {
                    throw new RuntimeException('Metadata 1618 melewati batas 8 MiB.');
                }
            }
        } finally {
            $stream->close();
        }
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $csv = $data['recordings_csv'] ?? null;
        if (! is_array($csv) || ! is_int($csv['rows'] ?? null) || $csv['rows'] < 0 || $csv['rows'] > 1000000) {
            throw new RuntimeException('Metadata ekspor CSV 1618 tidak valid.');
        }
        $observed = Robotics1618Csv::date($csv['fetched_utc'] ?? '');
        Robotics1618Csv::date($data['generated_utc'] ?? '');
        if ($observed->greaterThan(CarbonImmutable::now('UTC')->addMinutes(5))) {
            throw new RuntimeException('Jam sumber 1618 berada di masa depan.');
        }

        return $data;
    }

    public function refresh(): array
    {
        $before = $this->metadata();
        $this->request(self::BASE.'/refresh', [])->toPsrResponse()->getBody()->close();
        $until = microtime(true) + min(180, max(0, (int) config('services.robotics1618.refresh_wait', 30)));
        do {
            $fresh = $this->metadata();
            $published = $fresh['export_utc'] ?? null;
            if ($published && ($published !== ($before['export_utc'] ?? null) ||
                Robotics1618Csv::date($published)->greaterThan(CarbonImmutable::now('UTC')->subMinutes(15)))) {
                return $fresh;
            }
            if (microtime(true) >= $until) {
                return $fresh; // CSV collection and dashboard refresh have independent clocks.
            }
            sleep(5);
        } while (true);
    }

    public function download(string $path): string
    {
        $stream = $this->request($this->root.'recordings.csv')->toPsrResponse()->getBody();
        $file = fopen($path, 'wb');
        $size = 0;
        try {
            while (! $stream->eof()) {
                $this->checkDeadline();
                $chunk = $stream->read(65536);
                $size += strlen($chunk);
                if ($size > 64 * 1024 * 1024) {
                    throw new RuntimeException('CSV 1618 melewati batas 64 MiB.');
                }
                if (fwrite($file, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('Penyimpanan sementara CSV gagal.');
                }
            }
        } finally {
            fclose($file);
            $stream->close();
        }
        if (! $size) {
            throw new RuntimeException('CSV 1618 kosong.');
        }

        return hash_file('sha256', $path);
    }
}
