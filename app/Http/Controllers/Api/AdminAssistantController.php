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

        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized Access. Fitur hanya untuk Admin.'], 403);
        }

        $userMessage = $request->input('message');
        if (!$userMessage) {
            return response()->json(['reply' => 'Pesan tidak boleh kosong.'], 400);
        }

        // 2. PREPARE PAYLOAD FOR GEMINI API
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Konfigurasi GEMINI_API_KEY belum diatur di server.'], 500);
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

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
                    ]
                    // Tambahkan fungsi operasional lain di sini nantinya
                ]
            ]
        ];

        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => "Kamu adalah \"KameraBot\", Asisten Virtual cerdas untuk tim internal KameraKita. 
Tugas utamamu adalah membantu admin dan manajemen untuk memantau laporan kerja harian, statistik video, serta eksekusi payroll.

Daftar Tools & Skills MCP Kamerakita:
1. Pencarian & Inspeksi Data (search_partner & fetch_records)
2. Agregasi & Analisis Laporan (aggregate_records & qc_stats)
3. Rekonsiliasi Otomatis (auto_reconcile_proportional)
4. Asisten Penggajian & Payroll (payroll_assistant)
5. Detektor Anomali (anomaly_detector)
6. Klasemen Mitra Teratas (top_partners)
7. Eksekusi Massal Aman (execute_action)
8. Integrasi WhatsApp Instan (send_wa)
9. Persetujuan Batch (batch_reconcile_by_email)
10. Pembatalan Persetujuan (revert_reconcile_by_email)

Aturan Eksekusi Alat (Wajib Dipatuhi):
1. OTONOMI PENUH: Gunakan alat pembacaan data secara mandiri tanpa perlu meminta izin.
2. DOUBLE CHECK POINT: Sebelum mengeksekusi aksi pengubahan data database, kamu WAJIB meminta konfirmasi dengan bertanya singkat: \"Apakah Kakak yakin ingin menyetujui data ini?\". Jika dijawab \"Ya\", langsung eksekusi.

Aturan Komunikasi:
1. TO THE POINT: DILARANG KERAS basa-basi! Langsung berikan jawaban akhir.
2. NADA BICARA: Gunakan bahasa Indonesia santai tapi rapi, gunakan sapaan \"Kak\" atau \"Min\".
3. FORMAT: Gunakan *teks tebal* untuk angka/status, `monospace` untuk ID/email, dan bullet points (-).
4. RINGKASAN CERDAS: Jika hasil data sangat panjang, berikan total ringkasannya saja dan tawarkan rinciannya.
5. KEAMANAN: Jangan bocorkan ID sistem atau token rahasia kecuali diminta spesifik."]
                ]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userMessage]
                    ]
                ]
            ],
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
                $toolResult = [];

                // Router eksekusi fungsi
                switch ($functionName) {
                    case 'get_pending_qc_count':
                        // Menjalankan query ke database dengan aman
                        $count = \App\Models\VideoWorkReport::whereIn('qc_status', ['pending', 'on_review'])->count();
                        $toolResult = ['pending_qc_count' => $count];
                        break;
                    default:
                        $toolResult = ['error' => "Fungsi {$functionName} tidak dikenali di sistem."];
                        break;
                }

                // Kirim kembali hasil eksekusi (JSON) ke Gemini agar ia merangkai kalimat natural
                $secondPayload = $payload;
                $secondPayload['contents'][] = $candidates['content']; // Riwayat panggilan fungsi dari model
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
                    $finalText = $secondData['candidates'][0]['content']['parts'][0]['text'] ?? 'Eksekusi selesai.';
                    return response()->json(['reply' => $finalText]);
                }
            }

            // Jika tidak ada tool call, langsung kirim teks balasan
            return response()->json(['reply' => trim($replyText) ?: 'Instruksi selesai diproses.']);

        } catch (\Exception $e) {
            Log::error('AI Assistant Exception', ['error' => $e->getMessage()]);
            return response()->json(['reply' => 'Koneksi ke sistem AI terputus. Silakan coba lagi.'], 500);
        }
    }
}
