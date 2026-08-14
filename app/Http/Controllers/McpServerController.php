<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Models\CapturedEmail;
use App\Services\CalculatePartnerMetricsService;
use Carbon\Carbon;

class McpServerController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->bearerToken();
        $expectedKey = env('MCP_SECRET_KEY', 'kamerakita-mcp-2026');

        if (!$token || $token !== $expectedKey) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32000,
                    'message' => 'Invalid or missing MCP_SECRET_KEY in Bearer token.'
                ]
            ], 401);
        }

        $method = $request->input('method');
        $id = $request->input('id');

        if (!$method) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32600,
                    'message' => 'Missing method parameter'
                ]
            ], 400);
        }

        try {
            if ($method === 'initialize') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => [
                            'tools' => (object)[]
                        ],
                        'serverInfo' => [
                            'name' => 'kamerakita-mcp',
                            'version' => '1.0.0'
                        ]
                    ]
                ]);
            }

            if ($method === 'notifications/initialized') {
                return response()->json(); // No response needed for notification
            }

            if ($method === 'tools/list') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'tools' => $this->getAvailableTools()
                    ]
                ]);
            }

            if ($method === 'tools/call') {
                $toolName = $request->input('params.name');
                $args = $request->input('params.arguments', []);
                $isSimulation = $request->input('params.arguments.is_simulation', false) || $request->input('is_simulation', false);

                if (!$toolName) {
                    throw new \Exception("Missing tool name in params.name");
                }

                if ($isSimulation) {
                    DB::beginTransaction();
                }

                $result = null;

                switch ($toolName) {
                    case 'search_partner':
                        $result = $this->searchPartner($args);
                        break;
                    case 'qc_stats':
                        $result = $this->qcStats($args);
                        break;
                    case 'execute_action':
                        $result = $this->executeAction($args);
                        break;
                    case 'auto_reconcile_proportional':
                        $result = $this->autoReconcileProportional($args);
                        break;
                    case 'payroll_assistant':
                        $result = $this->payrollAssistant($args);
                        break;
                    case 'anomaly_detector':
                        $result = $this->anomalyDetector($args);
                        break;
                    case 'top_partners':
                        $result = $this->topPartners($args);
                        break;
                    case 'send_wa':
                        $result = $this->sendWa($args);
                        break;
                    case 'fetch_records':
                        $result = $this->fetchRecords($args);
                        break;
                    case 'aggregate_records':
                        $result = $this->aggregateRecords($args);
                        break;
                    case 'batch_reconcile_by_email':
                        $result = $this->batchReconcileByEmail($args);
                        break;
                    default:
                        throw new \Exception("Unknown tool: {$toolName}");
                }

                if ($isSimulation) {
                    DB::rollBack();
                    $result = [
                        'simulation_status' => 'success',
                        'message' => 'Dry-run successful. No data was actually modified.',
                        'preview' => $result
                    ];
                }

                $jsonString = is_array($result) || is_object($result) ? json_encode($result) : (string)$result;
                
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $jsonString
                            ]
                        ],
                        'isError' => false
                    ]
                ]);
            }

            throw new \Exception("Unsupported method: {$method}");

        } catch (\Exception $e) {
            if (isset($isSimulation) && $isSimulation) {
                DB::rollBack();
            }
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    private function getAvailableTools()
    {
        return [
            [
                'name' => 'search_partner',
                'description' => 'Cari partner/worker berdasarkan nama atau email.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Nama atau email yang dicari']
                    ],
                    'required' => ['keyword']
                ]
            ],
            [
                'name' => 'qc_stats',
                'description' => 'Dapatkan statistik ringkasan dan daftar laporan video terbaru berdasarkan status atau tanggal.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Status QC (pending, approved, rejected)'],
                        'date' => ['type' => 'string', 'description' => 'Tanggal laporan YYYY-MM-DD']
                    ]
                ]
            ],
            [
                'name' => 'execute_action',
                'description' => 'Lakukan tindakan eksekusi seperti batch approve laporan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => ['type' => 'string', 'description' => 'Aksi yang akan dilakukan, contoh: batch_approve_reports'],
                        'payload' => ['type' => 'object', 'description' => 'Payload data untuk aksi']
                    ],
                    'required' => ['action', 'payload']
                ]
            ],
            [
                'name' => 'auto_reconcile_proportional',
                'description' => 'Otomatis bagikan kuota menit yang disetujui secara proporsional ke semua video pending partner.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'partner_id' => ['type' => 'string'],
                        'total_quota_minutes' => ['type' => 'integer']
                    ],
                    'required' => ['partner_id', 'total_quota_minutes']
                ]
            ],
            [
                'name' => 'payroll_assistant',
                'description' => 'Bantu operasi payroll: baca tagihan berjalan, atau tandai tagihan lunas.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => ['type' => 'string', 'description' => 'read_stats atau mark_paid']
                    ],
                    'required' => ['action']
                ]
            ],
            [
                'name' => 'anomaly_detector',
                'description' => 'Temukan anomali data seperti video tertahan atau durasi tidak wajar.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'anomaly_type' => ['type' => 'string', 'description' => 'all, high_duration, stuck_pending']
                    ]
                ]
            ],
            [
                'name' => 'top_partners',
                'description' => 'Dapatkan klasemen 20 partner teratas berdasarkan total unggahan video.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => ['type' => 'integer', 'description' => 'Jumlah maksimal partner (default 20)']
                    ]
                ]
            ],
            [
                'name' => 'send_wa',
                'description' => 'Kirim pesan teks WhatsApp instan ke nomor HP (otomatis diubah ke kode negara).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'phone' => ['type' => 'string', 'description' => 'Nomor HP tujuan (misal: 089536...)'],
                        'message' => ['type' => 'string', 'description' => 'Isi pesan teks yang ingin dikirimkan']
                    ],
                    'required' => ['phone', 'message']
                ]
            ],
            [
                'name' => 'fetch_records',
                'description' => 'Membaca/mengambil data dari tabel dengan filter dinamis.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'resource' => ['type' => 'string', 'description' => 'Tabel target (contoh: partners, users, video_work_reports, captured_emails)'],
                        'select' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Kolom yang diambil'],
                        'filters' => ['type' => 'object', 'description' => 'Filter dalam bentuk key-value (mendukung string/angka atau object {in:[]}, {between:[]})'],
                        'relations' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Relasi yang disertakan'],
                        'limit' => ['type' => 'integer'],
                        'offset' => ['type' => 'integer']
                    ],
                    'required' => ['resource']
                ]
            ],
            [
                'name' => 'aggregate_records',
                'description' => 'Menghitung aggregasi data dari tabel secara dinamis.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'resource' => ['type' => 'string', 'description' => 'Tabel target (contoh: video_work_reports)'],
                        'aggregations' => ['type' => 'object', 'description' => 'Aggregasi, contoh: {"total_submitted": {"sum": "submitted_duration_minutes"}}'],
                        'filters' => ['type' => 'object', 'description' => 'Filter dinamis, sama seperti fetch_records'],
                        'group_by' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Kolom pengelompokan']
                    ],
                    'required' => ['resource', 'aggregations']
                ]
            ],
            [
                'name' => 'batch_reconcile_by_email',
                'description' => 'Otomatis bagikan kuota menit yang disetujui secara proporsional ke semua video pending partner berdasarkan daftar email.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'data' => [
                            'type' => 'array', 
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string'],
                                    'target_minutes' => ['type' => 'integer']
                                ],
                                'required' => ['email', 'target_minutes']
                            ],
                            'description' => 'Daftar email dan target menit'
                        ],
                        'start_date' => ['type' => 'string', 'description' => 'Opsional. YYYY-MM-DD'],
                        'end_date' => ['type' => 'string', 'description' => 'Opsional. YYYY-MM-DD'],
                        'project_name' => ['type' => 'string', 'description' => 'Opsional. Nama project, misal: ATLAS']
                    ],
                    'required' => ['data']
                ]
            ]
        ];
    }

    private function searchPartner(array $args)
    {
        $keyword = $args['keyword'] ?? '';
        return User::where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->with(['partner'])
            ->get();
    }

    private function qcStats(array $args)
    {
        $status = $args['status'] ?? null;
        $date = $args['date'] ?? null;

        $query = VideoWorkReport::query();
        if ($status) $query->where('qc_status', $status);
        if ($date) $query->whereDate('submission_date', $date);

        $total = $query->count();
        $approved = (clone $query)->where('qc_status', 'approved')->count();
        $pending = (clone $query)->where('qc_status', 'pending')->count();
        $rejected = (clone $query)->where('qc_status', 'rejected')->count();

        return [
            'summary' => compact('total', 'approved', 'pending', 'rejected'),
            'latest' => $query->latest()->limit(10)->get()
        ];
    }

    private function executeAction(array $args)
    {
        $action = $args['action'] ?? null;
        $payload = $args['payload'] ?? [];

        if (in_array($action, ['delete', 'destroy', 'remove', 'forceDelete'])) {
            throw new \Exception("NO HARD DELETES RULE: Permintaan destruktif diblokir secara absolut oleh MCP Server.");
        }

        switch ($action) {
            case 'batch_approve_reports':
                $ids = $payload['report_ids'] ?? [];
                $minutes = $payload['approved_minutes'] ?? 0;
                
                $reports = VideoWorkReport::whereIn('id', $ids)->where('qc_status', 'pending')->get();
                $updatedCount = 0;
                
                foreach ($reports as $report) {
                    $safeMinutes = min($minutes, $report->submitted_duration_minutes ?? 0);
                    $report->update([
                        'qc_status' => 'approved',
                        'approved_duration_minutes' => $safeMinutes,
                        'verifier_notes' => '(MCP Bot) Approved via API',
                        'verified_at' => now(),
                    ]);
                    $updatedCount++;
                }
                return ['message' => "Berhasil menyetujui {$updatedCount} laporan secara batch."];

            case 'soft_delete_email':
                $id = $payload['email_id'] ?? null;
                $email = CapturedEmail::find($id);
                if ($email) {
                    $email->delete(); 
                    return ['message' => "Email {$id} disembunyikan (SoftDelete)."];
                }
                return ['message' => 'Email tidak ditemukan.'];
                
            default:
                throw new \Exception("Aksi tidak didukung: {$action}");
        }
    }

    private function autoReconcileProportional(array $args)
    {
        $partnerId = $args['partner_id'] ?? null;
        $totalQuota = $args['total_quota_minutes'] ?? 0;

        if (!$partnerId || $totalQuota <= 0) {
            throw new \Exception("partner_id dan total_quota_minutes (positif) wajib diisi.");
        }

        $pendingReports = VideoWorkReport::where('partner_id', $partnerId)
            ->where('qc_status', 'pending')
            ->get();

        if ($pendingReports->isEmpty()) {
            return ['message' => 'Tidak ada video pending untuk direkonsiliasi.'];
        }

        $totalSubmitted = $pendingReports->sum('submitted_duration_minutes');
        if ($totalSubmitted <= 0) {
            throw new \Exception("Total durasi submitted 0, tidak bisa membagi proporsi.");
        }

        $results = [];
        $remainingQuota = $totalQuota;

        foreach ($pendingReports as $index => $report) {
            $submitted = $report->submitted_duration_minutes;
            $proportion = $submitted / $totalSubmitted;
            
            if ($index === $pendingReports->count() - 1) {
                $allocated = $remainingQuota;
            } else {
                $allocated = (int) round($totalQuota * $proportion);
                $remainingQuota -= $allocated;
            }

            $allocated = min($allocated, $submitted);

            $report->update([
                'qc_status' => 'approved',
                'approved_duration_minutes' => $allocated,
                'verifier_notes' => '(MCP Bot) Proporsional Auto-Reconcile',
                'verified_at' => now(),
            ]);

            $results[] = [
                'report_id' => $report->id,
                'submitted' => $submitted,
                'allocated' => $allocated
            ];
        }

        return [
            'message' => 'Rekonsiliasi proporsional berhasil didistribusikan.',
            'total_allocated' => collect($results)->sum('allocated'),
            'details' => $results
        ];
    }

    private function batchReconcileByEmail(array $args)
    {
        $data = $args['data'] ?? [];
        $startDate = $args['start_date'] ?? null;
        $endDate = $args['end_date'] ?? null;
        $projectName = $args['project_name'] ?? null;

        if (empty($data) || !is_array($data)) {
            throw new \Exception("Parameter data wajib diisi dan berupa array.");
        }

        $results = [];
        $totalProcessedEmails = 0;
        $totalAllocatedMinutes = 0;

        foreach ($data as $item) {
            $email = $item['email'] ?? null;
            $targetMinutes = $item['target_minutes'] ?? 0;

            if (!$email || $targetMinutes <= 0) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Email kosong atau target_minutes <= 0'];
                continue;
            }

            $user = User::where('email', $email)->first();
            if (!$user || !$user->partner) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'User atau Partner tidak ditemukan'];
                continue;
            }

            $partnerId = $user->partner->id;

            $query = VideoWorkReport::where('partner_id', $partnerId)
                ->where('qc_status', 'pending');

            if ($startDate) {
                $query->whereDate('submission_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('submission_date', '<=', $endDate);
            }
            if ($projectName) {
                $query->where('project_name', $projectName);
            }

            $pendingReports = $query->get();

            if ($pendingReports->isEmpty()) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Tidak ada video pending di kriteria ini'];
                continue;
            }

            $totalSubmitted = $pendingReports->sum('submitted_duration_minutes');
            if ($totalSubmitted <= 0) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Total submitted duration = 0'];
                continue;
            }

            $remainingQuota = $targetMinutes;
            $allocatedForUser = 0;

            foreach ($pendingReports as $index => $report) {
                $submitted = $report->submitted_duration_minutes;
                $proportion = $submitted / $totalSubmitted;
                
                if ($index === $pendingReports->count() - 1) {
                    $allocated = $remainingQuota;
                } else {
                    $allocated = (int) round($targetMinutes * $proportion);
                    $remainingQuota -= $allocated;
                }

                $allocated = min($allocated, $submitted);
                $allocatedForUser += $allocated;

                $report->update([
                    'qc_status' => 'approved',
                    'approved_duration_minutes' => $allocated,
                    'verifier_notes' => '(MCP Bot) Batch Reconcile by Email',
                    'verified_at' => now(),
                ]);
            }

            $totalProcessedEmails++;
            $totalAllocatedMinutes += $allocatedForUser;
            $results[] = [
                'email' => $email, 
                'status' => 'success', 
                'allocated' => $allocatedForUser,
                'reports_affected' => $pendingReports->count()
            ];
        }

        return [
            'message' => "Selesai memproses batch reconcile.",
            'summary' => [
                'emails_processed' => $totalProcessedEmails,
                'total_allocated_minutes' => $totalAllocatedMinutes,
            ],
            'details' => $results
        ];
    }

    private function payrollAssistant(array $args)
    {
        $action = $args['action'] ?? 'read_stats'; 
        
        if ($action === 'read_stats') {
            $unpaidApproved = VideoWorkReport::where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->selectRaw('SUM(approved_duration_minutes) as total_minutes')
                ->first();
            
            return [
                'total_unpaid_approved_minutes' => $unpaidApproved->total_minutes ?? 0,
                'message' => 'Ringkasan tagihan berjalan.'
            ];
        }

        if ($action === 'mark_paid') {
            $updated = VideoWorkReport::where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'verifier_notes' => DB::raw("CONCAT(COALESCE(verifier_notes, ''), ' | (MCP Bot) Marked Paid')")
                ]);

            return ['message' => "Berhasil menandai {$updated} laporan menjadi Paid."];
        }

        throw new \Exception("Unknown payroll action: {$action}");
    }

    private function anomalyDetector(array $args)
    {
        $type = $args['anomaly_type'] ?? 'all';
        $anomalies = [];

        if (in_array($type, ['all', 'high_duration'])) {
            $highDuration = VideoWorkReport::where('submitted_duration_minutes', '>', 500)
                ->where('qc_status', 'pending')
                ->with('partner.user')
                ->get();
            $anomalies['high_duration'] = $highDuration;
        }

        if (in_array($type, ['all', 'stuck_pending'])) {
            $stuckPending = VideoWorkReport::where('qc_status', 'pending')
                ->where('created_at', '<', Carbon::now()->subDays(7))
                ->with('partner.user')
                ->get();
            $anomalies['stuck_pending'] = $stuckPending;
        }

        return [
            'message' => 'Pemindaian anomali selesai.',
            'anomalies_found' => count($anomalies['high_duration'] ?? []) + count($anomalies['stuck_pending'] ?? []),
            'data' => $anomalies
        ];
    }

    private function topPartners(array $args)
    {
        $limit = $args['limit'] ?? 20;

        $partners = Partner::with('user')
            ->withCount('videoWorkReports as total_reports')
            ->withSum('videoWorkReports as total_submitted', 'submitted_duration_minutes')
            ->withSum('videoWorkReports as total_approved', 'approved_duration_minutes')
            ->orderByDesc('total_reports')
            ->limit($limit)
            ->get()
            ->map(function ($partner) {
                return [
                    'name' => $partner->user->name ?? 'Unknown',
                    'email' => $partner->user->email ?? 'Unknown',
                    'wa_number' => $partner->whatsapp_number ?? '-',
                    'total_reports' => $partner->total_reports,
                    'total_submitted_minutes' => $partner->total_submitted ?? 0,
                    'total_approved_minutes' => $partner->total_approved ?? 0,
                ];
            });

        return [
            'message' => "Top {$limit} Partners by Reports",
            'data' => $partners
        ];
    }

    private function sendWa(array $args)
    {
        $phone = $args['phone'] ?? null;
        $message = $args['message'] ?? null;

        if (!$phone || !$message) {
            throw new \Exception("Parameter phone dan message wajib diisi.");
        }

        $waService = new \App\Services\HandcapWaService();
        $response = $waService->sendMessage($phone, $message, 'high'); // Use high priority

        return [
            'status' => 'success',
            'message' => 'Pesan WA berhasil dikirim',
            'gateway_response' => $response
        ];
    }

    private function getQueryForResource($resource)
    {
        switch ($resource) {
            case 'users': return User::query();
            case 'partners': return Partner::query();
            case 'video_work_reports': return VideoWorkReport::query();
            case 'captured_emails': return CapturedEmail::query();
            default: throw new \Exception("Resource not allowed: {$resource}");
        }
    }

    private function applyFilters($query, $filters)
    {
        if (!is_array($filters)) return;
        foreach ($filters as $column => $condition) {
            if (is_array($condition)) {
                if (isset($condition['in'])) {
                    $query->whereIn($column, $condition['in']);
                }
                if (isset($condition['between']) && is_array($condition['between']) && count($condition['between']) === 2) {
                    $query->whereBetween($column, $condition['between']);
                }
                if (isset($condition['>'])) $query->where($column, '>', $condition['>']);
                if (isset($condition['<'])) $query->where($column, '<', $condition['<']);
                if (isset($condition['>='])) $query->where($column, '>=', $condition['>=']);
                if (isset($condition['<='])) $query->where($column, '<=', $condition['<=']);
                if (isset($condition['!='])) $query->where($column, '!=', $condition['!=']);
                if (isset($condition['like'])) $query->where($column, 'LIKE', $condition['like']);
            } else {
                $query->where($column, $condition);
            }
        }
    }

    private function fetchRecords(array $args)
    {
        $resource = $args['resource'] ?? null;
        if (!$resource) throw new \Exception("Resource is required");

        $query = $this->getQueryForResource($resource);

        if (isset($args['select']) && is_array($args['select'])) {
            $query->select($args['select']);
        }

        if (isset($args['relations']) && is_array($args['relations'])) {
            $query->with($args['relations']);
        }

        if (isset($args['filters'])) {
            $this->applyFilters($query, $args['filters']);
        }

        $limit = $args['limit'] ?? 100; // Default limit
        $offset = $args['offset'] ?? 0;

        $totalCount = $query->count();
        
        $data = $query->limit(min(1000, $limit))->offset($offset)->get();

        return [
            'total' => $totalCount,
            'count' => $data->count(),
            'data' => $data
        ];
    }

    private function aggregateRecords(array $args)
    {
        $resource = $args['resource'] ?? null;
        if (!$resource) throw new \Exception("Resource is required");

        $aggregations = $args['aggregations'] ?? [];
        if (empty($aggregations) || !is_array($aggregations)) {
            throw new \Exception("Aggregations object is required");
        }

        // Use direct DB table query to avoid Eloquent appends accessors error on aggregated/partial columns
        $tableMap = [
            'users' => 'users',
            'partners' => 'partners',
            'video_work_reports' => 'video_work_reports',
            'captured_emails' => 'captured_emails',
        ];

        if (!isset($tableMap[$resource])) {
            throw new \Exception("Resource not allowed: {$resource}");
        }

        $query = DB::table($tableMap[$resource]);

        if (isset($args['filters'])) {
            $this->applyFilters($query, $args['filters']);
        }

        $groupBy = $args['group_by'] ?? [];
        if (!empty($groupBy)) {
            $query->groupBy($groupBy);
            $selects = $groupBy;
        } else {
            $selects = [];
        }

        foreach ($aggregations as $alias => $operation) {
            foreach ($operation as $type => $column) {
                $type = strtoupper($type);
                if (in_array($type, ['SUM', 'COUNT', 'AVG', 'MIN', 'MAX'])) {
                    $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                    $cleanAlias = preg_replace('/[^a-zA-Z0-9_]/', '', $alias);
                    $selects[] = DB::raw("{$type}({$cleanCol}) as {$cleanAlias}");
                }
            }
        }

        if (!empty($selects)) {
            $query->select($selects);
        }

        return [
            'data' => $query->get()
        ];
    }
}
