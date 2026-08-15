<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAssistantController extends Controller
{
    public function handle(Request $request)
    {
        // 1. SECURITY & ROLE CHECK
        $user = $request->user();
        
        // Asumsi fallback jika diakses via route web yang disatukan ke api
        if (!$user && auth()->check()) {
            $user = auth()->user();
        }

        if (!$user || !$user->hasFullAdminAccess()) {
            return response()->json(['message' => 'Unauthorized Access. Fitur hanya untuk Admin.'], 403);
        }

        $userMessage = $request->input('message');
        $history = $request->input('history', []); // Menerima histori dari frontend
        
        if (!$userMessage) {
            return response()->json(['reply' => 'Pesan tidak boleh kosong.'], 400);
        }

        // 2. PREPARE PAYLOAD FOR GEMINI API
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Konfigurasi GEMINI_API_KEY belum diatur di server.'], 500);
        }

        // Resolve model dynamically and cache it
        $modelName = $this->getBestGeminiModel($apiKey);
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

        // Define Tools / Functions that Gemini can call
        $tools = [
            [
                'function_declarations' => [
                    [
                        'name' => 'get_pending_qc_count',
                        'description' => 'Menghitung jumlah laporan video dari worker yang masih pending atau on_review (belum di-QC).',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => (object)[]
                        ]
                    ],
                    [
                        'name' => 'search_user',
                        'description' => 'Mencari data pengguna atau partner (nama, email, role, id) berdasarkan kata kunci nama.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'keyword' => [
                                    'type' => 'STRING',
                                    'description' => 'Nama atau potongan nama user yang ingin dicari (contoh: randy)'
                                ]
                            ],
                            'required' => ['keyword']
                        ]
                    ],
                    [
                        'name' => 'get_user_stats',
                        'description' => 'Mendapatkan statistik laporan video dari seorang partner berdasarkan ID-nya (total disetujui, belum dibayar, pending).',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'partner_id' => [
                                    'type' => 'INTEGER',
                                    'description' => 'ID user partner (didapat dari hasil pencarian user)'
                                ]
                            ],
                            'required' => ['partner_id']
                        ]
                    ]
                ]
            ]
        ];

        // Format history sesuai dengan syarat API Gemini (harus dimulai dari user, dan harus bergantian user-model)
        $contents = [];
        $lastRole = null;
        
        foreach ($history as $msg) {
            // Abaikan sapaan awal sistem atau pesan error jaringan yang tidak penting
            if (empty($contents) && $msg['role'] === 'model') {
                continue;
            }
            if ($msg['role'] === 'model' && str_contains($msg['text'], 'Maaf, terjadi kesalahan')) {
                continue;
            }
            
            $currentRole = $msg['role'] === 'model' ? 'model' : 'user';
            
            // Jika ada dua pesan beruntun dari role yang sama, gabungkan jadi satu 
            // karena Gemini wajib bergantian user -> model -> user -> model
            if ($lastRole === $currentRole) {
                $contents[count($contents) - 1]['parts'][0]['text'] .= "\n\n" . $msg['text'];
            } else {
                $contents[] = [
                    'role' => $currentRole,
                    'parts' => [['text' => $msg['text']]]
                ];
                $lastRole = $currentRole;
            }
        }

        // Sisipkan pesan user yang baru
        if ($lastRole === 'user') {
            $contents[count($contents) - 1]['parts'][0]['text'] .= "\n\n" . $userMessage;
        } else {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]]
            ];
        }

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => "Kamu adalah \"KameraBot\", Asisten Virtual cerdas untuk tim internal KameraKita. 
Tugas utamamu adalah membantu admin dan manajemen untuk memantau laporan kerja harian, statistik video, serta eksekusi payroll.

Daftar Tools & Skills MCP Kamerakita (Internal):
1. get_pending_qc_count: Menghitung total laporan pending QC.
2. search_user: Mencari data partner/user berdasarkan nama.
3. get_user_stats: Mengambil statistik laporan spesifik partner.

Aturan Eksekusi Alat (Wajib Dipatuhi):
1. OTONOMI PENUH: Gunakan alat pembacaan data secara mandiri tanpa perlu meminta izin. Kombinasikan alat jika perlu (misal: cari user dulu, lalu panggil get_user_stats pakai ID-nya).
2. DOUBLE CHECK POINT: Sebelum mengeksekusi aksi pengubahan data database, kamu WAJIB meminta konfirmasi dengan bertanya singkat: \"Apakah Kakak yakin ingin menyetujui data ini?\". Jika dijawab \"Ya\", langsung eksekusi.

Aturan Komunikasi:
1. TO THE POINT: DILARANG KERAS basa-basi! Langsung berikan jawaban akhir.
2. NADA BICARA: Gunakan bahasa Indonesia santai tapi rapi, gunakan sapaan \"Kak\" atau \"Min\".
3. FORMAT: Gunakan *teks tebal* untuk angka/status, `monospace` untuk ID/email, dan bullet points (-).
4. RINGKASAN CERDAS: Jika hasil data sangat panjang, berikan total ringkasannya saja dan tawarkan rinciannya.
5. KEAMANAN: Jangan bocorkan ID sistem atau token rahasia kecuali diminta spesifik."]
                ]
            ],
            'contents' => $contents,
            'tools' => $tools
        ];

        try {
            // 3. EXECUTE REQUEST TO GEMINI
            $response = Http::timeout(30)->post($endpoint, $payload);

            // 4. RATE LIMITING HANDLER (SANGAT KRUSIAL)
            if ($response->status() === 429) {
                return response()->json([
                    'reply' => 'Sistem sedang memproses terlalu banyak data dalam satu menit. Mohon tunggu sekitar 30 detik sebelum memberikan perintah baru.'
                ], 429);
            }

            if (!$response->successful()) {
                Log::error('Gemini API Error', ['body' => $response->body()]);
                return response()->json(['reply' => 'Maaf, terjadi kesalahan internal saat menghubungi AI engine.'], 500);
            }

            $data = $response->json();
            $candidates = $data['candidates'][0] ?? null;

            if (!$candidates) {
                return response()->json(['reply' => 'Tidak ada respon dari server AI.']);
            }

            // 5. TOOL CALLS HANDLING (THE BRIDGE)
            $parts = $candidates['content']['parts'] ?? [];
            $toolCall = null;
            $replyText = '';

            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $toolCall = $part['functionCall'];
                } elseif (isset($part['text'])) {
                    $replyText .= $part['text'];
                }
            }

            // Jika AI memutuskan untuk memanggil fungsi internal sistem
            if ($toolCall) {
                $functionName = $toolCall['name'];
                $toolArgs = $toolCall['args'] ?? [];
                $toolResult = [];

                // Router eksekusi fungsi
                switch ($functionName) {
                    case 'get_pending_qc_count':
                        $count = \App\Models\VideoWorkReport::whereIn('qc_status', ['pending', 'on_review'])->count();
                        $toolResult = ['pending_qc_count' => $count];
                        break;
                    case 'search_user':
                        $keyword = $toolArgs['keyword'] ?? '';
                        if (!$keyword) {
                            $toolResult = ['error' => 'Keyword pencarian kosong.'];
                        } else {
                            $users = \App\Models\User::where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->select('id', 'name', 'email', 'role')
                                ->limit(5)
                                ->get();
                            $toolResult = ['users_found' => $users->toArray(), 'total' => $users->count()];
                        }
                        break;
                    case 'get_user_stats':
                        $partnerId = $toolArgs['partner_id'] ?? null;
                        if (!$partnerId) {
                            $toolResult = ['error' => 'Partner ID harus diisi.'];
                        } else {
                            $stats = \App\Models\VideoWorkReport::where('partner_id', $partnerId)
                                ->selectRaw('
                                    count(*) as total_reports,
                                    sum(case when qc_status = "approved" then 1 else 0 end) as total_approved,
                                    sum(case when payment_status = "paid" then 1 else 0 end) as total_paid,
                                    sum(approved_duration_minutes) as total_approved_minutes
                                ')->first();
                            $toolResult = ['partner_id' => $partnerId, 'stats' => $stats ? $stats->toArray() : null];
                        }
                        break;
                    default:
                        $toolResult = ['error' => "Fungsi {$functionName} tidak dikenali di sistem."];
                        break;
                }

                // Kirim kembali hasil eksekusi (JSON) ke Gemini agar ia merangkai kalimat natural
                $secondPayload = $payload;
                
                // Cegah bug json_encode mengubah empty object {} menjadi empty array [] pada args
                $modelContent = $candidates['content'];
                if (isset($modelContent['parts'])) {
                    foreach ($modelContent['parts'] as &$p) {
                        if (isset($p['functionCall']['args']) && is_array($p['functionCall']['args']) && empty($p['functionCall']['args'])) {
                            $p['functionCall']['args'] = new \stdClass();
                        }
                    }
                }
                
                $secondPayload['contents'][] = $modelContent; // Riwayat panggilan fungsi dari model
                $secondPayload['contents'][] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $functionName,
                                'response' => ['result' => $toolResult]
                            ]
                        ]
                    ]
                ];

                $secondResponse = Http::timeout(30)->post($endpoint, $secondPayload);

                // Handle rate limit di request kedua
                if ($secondResponse->status() === 429) {
                    return response()->json([
                        'reply' => 'Sistem sedang memproses terlalu banyak data dalam satu menit. Mohon tunggu sekitar 30 detik sebelum memberikan perintah baru.'
                    ], 429);
                }

                if ($secondResponse->successful()) {
                    $secondData = $secondResponse->json();
                    $parts2 = $secondData['candidates'][0]['content']['parts'] ?? [];
                    $finalText = '';
                    
                    foreach ($parts2 as $p) {
                        if (isset($p['text'])) {
                            $finalText .= $p['text'];
                        }
                    }
                    
                    if (empty(trim($finalText))) {
                        // Jika model masih mencoba memanggil fungsi lagi atau bingung
                        $finalText = "Sistem saat ini belum dilengkapi dengan fungsi untuk mengeksekusi instruksi tersebut secara spesifik.";
                    }
                    
                    return response()->json(['reply' => trim($finalText)]);
                }
            }

            // Jika tidak ada tool call, langsung kirim teks balasan
            return response()->json(['reply' => trim($replyText) ?: 'Instruksi selesai diproses.']);

        } catch (\Exception $e) {
            Log::error('AI Assistant Exception', ['error' => $e->getMessage()]);
            return response()->json(['reply' => 'Koneksi ke sistem AI terputus. Silakan coba lagi.'], 500);
        }
    }

    private function getBestGeminiModel($apiKey)
    {
        return cache()->remember('best_gemini_model', 86400, function() use ($apiKey) {
            $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
            if ($response->successful()) {
                $models = $response->json('models') ?? [];
                
                $validModels = [];
                // Kumpulkan model flash yang stabil (mendukung generateContent)
                foreach ($models as $model) {
                    $methods = $model['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods)) {
                        $name = str_replace('models/', '', $model['name']);
                        if (str_contains($name, 'gemini') && str_contains($name, 'flash') && !str_contains($name, 'preview')) {
                            $validModels[] = $name;
                        }
                    }
                }
                
                // Sortir menurun secara natural agar gemini-3.5/3.7 berada di atas gemini-2.5
                if (!empty($validModels)) {
                    usort($validModels, function($a, $b) {
                        return strnatcmp($b, $a); 
                    });
                    
                    // Hindari model 2.5 yang sudah ditarik Google untuk user baru
                    foreach ($validModels as $vm) {
                        if (!str_starts_with($vm, 'gemini-2.')) {
                            return $vm;
                        }
                    }
                    return $validModels[0];
                }
                
                // Fallback: ambil model gemini apa saja
                foreach ($models as $model) {
                    $methods = $model['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods) && str_contains($model['name'], 'gemini')) {
                        return str_replace('models/', '', $model['name']);
                    }
                }
            }
            // Fallback default
            return 'gemini-3.5-flash-lite';
        });
    }
}
