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
        // 1. Verify Authentication
        $secret = env('MCP_SECRET_KEY', 'kamerakita-mcp-2026');
        $token = $request->bearerToken();
        
        if ($token !== $secret) {
            return response()->json(['error' => 'Unauthorized. Invalid MCP Secret Key.'], 401);
        }

        $tool = $request->input('tool');
        $args = $request->input('arguments', []);
        $isSimulation = $request->input('is_simulation', false);

        try {
            if ($isSimulation) {
                DB::beginTransaction();
            }

            $result = null;

            switch ($tool) {
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
                default:
                    return response()->json(['error' => "Unknown tool: {$tool}"], 400);
            }

            if ($isSimulation) {
                DB::rollBack();
                return response()->json([
                    'status' => 'simulation_success',
                    'message' => 'Dry-run successful. No data was actually modified.',
                    'preview' => $result
                ]);
            }

            if (isset($result['status']) && $result['status'] === 'error') {
                return response()->json($result, 400);
            }

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            if ($isSimulation) {
                DB::rollBack();
            }
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
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
}
