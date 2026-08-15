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

        // 1. AMBIL TOOLS DARI MCP SERVER (DINAMIS)
        $mcpController = app(\App\Http\Controllers\McpServerController::class);
        $mcpToken = env('MCP_SECRET_KEY', 'kamerakita-mcp-2026');

        $reqList = \Illuminate\Http\Request::create('/api/mcp', 'POST', [
            'method' => 'tools/list',
            'id' => uniqid()
        ]);
        $reqList->headers->set('Authorization', 'Bearer ' . $mcpToken);
        $resList = $mcpController->handle($reqList);
        $mcpData = json_decode($resList->getContent(), true);
        $mcpTools = $mcpData['result']['tools'] ?? [];

        // Map format MCP ke format Gemini Function Declarations
        $geminiTools = [];
        foreach ($mcpTools as $t) {
            $gt = [
                'name' => $t['name'],
                'description' => $t['description'] ?? '',
            ];
            if (isset($t['parameters'])) {
                $p = $t['parameters'];
                if (isset($p['type'])) $p['type'] = strtoupper($p['type']);
                if (isset($p['properties'])) {
                    if (empty($p['properties'])) {
                        $p['properties'] = new \stdClass();
                    } else {
                        foreach ($p['properties'] as $key => &$prop) {
                            if (isset($prop['type'])) $prop['type'] = strtoupper($prop['type']);
                            if (isset($prop['items']['type'])) $prop['items']['type'] = strtoupper($prop['items']['type']);
                        }
                    }
                } else {
                    $p['properties'] = new \stdClass();
                }
                $gt['parameters'] = $p;
            } else {
                $gt['parameters'] = ['type' => 'OBJECT', 'properties' => new \stdClass()];
            }
            $geminiTools[] = $gt;
        }

        $tools = [
            ['function_declarations' => $geminiTools]
        ];

        // Format history sesuai dengan syarat API Gemini
        $contents = [];
        $lastRole = null;
        
        foreach ($history as $msg) {
            if (empty($contents) && $msg['role'] === 'model') continue;
            if ($msg['role'] === 'model' && str_contains($msg['text'], 'Maaf, terjadi kesalahan')) continue;
            
            $currentRole = $msg['role'] === 'model' ? 'model' : 'user';
            
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
Tugas utamamu adalah membantu admin dan manajemen memantau laporan kerja, statistik video, payroll, dan mengeksekusi operasi sistem.

Aturan Eksekusi Alat (Wajib Dipatuhi):
1. OTONOMI PENUH: Gunakan alat pembacaan data secara mandiri tanpa perlu meminta izin. Kombinasikan alat jika perlu (misal: cari user dulu, lalu panggil qc_stats pakai ID-nya).
2. DOUBLE CHECK POINT: Sebelum mengeksekusi alat yang bersifat mengubah data (seperti execute_action, batch_reconcile), kamu WAJIB meminta konfirmasi terlebih dahulu dengan bertanya singkat: \"Apakah Kakak yakin ingin mengeksekusi ini?\". Jika dijawab \"Ya\", langsung jalankan alatnya.

Aturan Komunikasi:
1. TO THE POINT: DILARANG KERAS basa-basi! Langsung berikan jawaban akhir.
2. NADA BICARA: Gunakan bahasa Indonesia santai tapi rapi, gunakan sapaan \"Kak\" atau \"Min\".
3. FORMAT: Gunakan *teks tebal* untuk angka/status, `monospace` untuk ID/email, dan bullet points (-).
4. RINGKASAN CERDAS: Jika hasil data sangat panjang, berikan total ringkasannya saja dan tawarkan rinciannya."]
                ]
            ],
            'contents' => $contents,
            'tools' => $tools
        ];

        try {
            // 3. EXECUTE REQUEST TO GEMINI
            $response = Http::timeout(45)->post($endpoint, $payload);

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

            // Jika AI memutuskan untuk memanggil fungsi internal sistem (VIA MCP SERVER)
            if ($toolCall) {
                $functionName = $toolCall['name'];
                $toolArgs = $toolCall['args'] ?? [];
                
                // Teruskan eksekusi fungsi ke McpServerController secara langsung
                $reqCall = \Illuminate\Http\Request::create('/api/mcp', 'POST', [
                    'method' => 'tools/call',
                    'id' => uniqid(),
                    'params' => [
                        'name' => $functionName,
                        'arguments' => $toolArgs
                    ]
                ]);
                $reqCall->headers->set('Authorization', 'Bearer ' . $mcpToken);
                
                $resCall = $mcpController->handle($reqCall);
                $callData = json_decode($resCall->getContent(), true);
                
                $toolResult = [];
                if (isset($callData['error'])) {
                    $toolResult = ['error' => $callData['error']['message'] ?? 'Unknown MCP Error'];
                } else {
                    // Ekstrak hasil teks dari response standar MCP
                    $contentStr = $callData['result']['content'][0]['text'] ?? '{}';
                    $toolResult = json_decode($contentStr, true) ?? ['result' => $contentStr];
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
