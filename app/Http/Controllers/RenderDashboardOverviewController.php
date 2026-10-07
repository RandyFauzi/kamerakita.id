<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\CalculatePartnerMetricsService;
use App\Services\PartnerActivityStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RenderDashboardOverviewController extends Controller
{
    protected $metricsService;
    protected $leaderboardService;

    public function __construct(
        CalculatePartnerMetricsService $metricsService,
        \App\Services\LeaderboardService $leaderboardService
    ) {
        $this->metricsService = $metricsService;
        $this->leaderboardService = $leaderboardService;
    }

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        
        // Find if user is linked to a partner record
        $partner = Partner::where('user_id', $user->id)->first();

        if ($partner) {
            $partner = app(PartnerActivityStatusService::class)->syncPartner($partner);

            if ($partner->partner_role === 'worker') {
                $metrics = $this->metricsService->getWorkerMetrics($partner);
                
                // Get latest submissions
                $reports = VideoWorkReport::where('partner_id', $partner->id)
                    ->orderBy('submission_date', 'desc')
                    ->limit(10)
                    ->get();
                
                // Check if user is rank 1, 2, or 3 this week using centralized service
                $userRank = $this->leaderboardService->getPartnerWeeklyRank($partner->id, 3);
                $isRankOneThisWeek = $userRank === 1;

                // Astro Data Check
                $isAstro = str_contains($partner->group_name ?? '', 'ASTRO') || str_contains($partner->group ?? '', 'ASTRO');
                $astroStats = null;
                
                if ($isAstro) {
                    $atlasWorker = \App\Models\AtlasWorker::where('atlas_email', $user->email)->first();
                    if ($atlasWorker) {
                        $statsQuery = \App\Models\AtlasTask::where('atlas_worker_id', $atlasWorker->id);
                        $totalWorked = $statsQuery->sum('worked_minutes');
                        $totalApproved = $statsQuery->sum('approved_minutes');
                        $totalReview = $statsQuery->sum('review_minutes');
                        $totalRejected = $statsQuery->sum('rejected_minutes');
                        
                        $approvalRate = $totalWorked > 0 ? round(($totalApproved / $totalWorked) * 100) : 0;
                        
                        $todayWorked = \App\Models\AtlasTask::where('atlas_worker_id', $atlasWorker->id)
                                        ->whereDate('task_date', \Carbon\Carbon::today())
                                        ->sum('worked_minutes');

                        $astroStats = [
                            'worked_hours' => floor($totalWorked / 60) . 'h ' . round($totalWorked % 60) . 'm',
                            'approved_hours' => floor($totalApproved / 60) . 'h ' . round($totalApproved % 60) . 'm',
                            'review_hours' => floor($totalReview / 60) . 'h ' . round($totalReview % 60) . 'm',
                            'rejected_hours' => floor($totalRejected / 60) . 'h ' . round($totalRejected % 60) . 'm',
                            'approval_rate' => $approvalRate,
                            'total_worked' => $totalWorked,
                            'today_worked' => $todayWorked,
                        ];
                    } else {
                        $astroStats = [
                            'worked_hours' => '0h 0m',
                            'approved_hours' => '0h 0m',
                            'review_hours' => '0h 0m',
                            'rejected_hours' => '0h 0m',
                            'approval_rate' => 0,
                            'total_worked' => 0,
                            'today_worked' => 0,
                        ];
                    }
                }

                return view('dashboard.worker', compact('partner', 'metrics', 'reports', 'isRankOneThisWeek', 'userRank', 'isAstro', 'astroStats'));
            }

            if ($partner->partner_role === 'mitra') {
                $metrics = $this->metricsService->getMitraMetrics($partner);

                return view('dashboard.mitra', compact('partner', 'metrics'));
            }

            if ($partner->partner_role === 'rekruter') {
                // Auto-generate referral code if Rekruter doesn't have one
                if (empty($partner->referral_code)) {
                    $partner->referral_code = 'REF-' . strtoupper(str_replace('-', '', $partner->mitra_id ?? 'RKR')) . '-' . strtoupper(\Illuminate\Support\Str::random(4));
                    $partner->save();
                }

                $metrics = $this->metricsService->getRekruterMetrics($partner);

                return view('dashboard.rekruter', compact('partner', 'metrics'));
            }
        }

        // Check if user is an internal admin/finance user
        if ($user->hasFullAdminAccess() || $user->role === 'finance') {
            $metrics = $this->metricsService->getGlobalMetrics();
            
            $latestReports = VideoWorkReport::with(['partner'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $clientInvoices = \App\Models\Invoice::with('client')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $monthlyData = collect(\Illuminate\Support\Facades\Cache::remember('admin_monthly_data_v2', 600, function () {
                $isMysql = \Illuminate\Support\Facades\DB::getDriverName() === 'mysql';
                $groupByRaw = $isMysql ? "DATE_FORMAT(submission_date, '%Y-%m')" : "strftime('%Y-%m', submission_date)";
                return VideoWorkReport::select(
                        \Illuminate\Support\Facades\DB::raw("$groupByRaw as month"),
                        \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN qc_status = 'approved' THEN approved_duration_minutes ELSE 0 END) as approved_minutes"),
                        \Illuminate\Support\Facades\DB::raw("SUM(submitted_duration_minutes) as submitted_minutes")
                    )
                    ->where('submission_date', '>=', now()->subMonths(6)->startOfMonth())
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get()
                    ->toArray();
            }));

            $dailyAverageData = collect(\Illuminate\Support\Facades\Cache::remember('admin_daily_average_data', 600, function () {
                return VideoWorkReport::select(
                        'submission_date',
                        \Illuminate\Support\Facades\DB::raw("AVG(submitted_duration_minutes) as avg_minutes")
                    )
                    ->where('submission_date', '>=', now()->subDays(7)->toDateString())
                    ->groupBy('submission_date')
                    ->orderBy('submission_date', 'asc')
                    ->get()
                    ->toArray();
            }));

            return view('dashboard.admin', compact('metrics', 'latestReports', 'clientInvoices', 'monthlyData', 'dailyAverageData'));
        }

        // Default fallback dashboard
        return view('dashboard.fallback', compact('user'));
    }
}
