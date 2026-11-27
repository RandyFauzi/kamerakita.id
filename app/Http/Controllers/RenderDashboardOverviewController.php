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
                
                // Get latest submissions from AtlasTask
                $atlasWorkerIds = \App\Models\AtlasWorker::where(function ($q) use ($partner) {
                    $q->where('user_id', $partner->user_id)
                      ->orWhere('atlas_email', $partner->email);
                })->pluck('id');
                
                $reports = \App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
                    ->orderBy('task_date', 'desc')
                    ->limit(10)
                    ->get();
                
                // Check if user is rank 1, 2, or 3 this week within their category using centralized service
                $userCategory = $this->leaderboardService->resolvePartnerCategory($partner);
                $userRank = $this->leaderboardService->getPartnerWeeklyRank($partner->id, 3, $userCategory);
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

                        $highestDailyMinutes = \App\Models\AtlasTask::where('atlas_worker_id', $atlasWorker->id)
                                        ->selectRaw('SUM(worked_minutes) as daily_total')
                                        ->groupBy('task_date')
                                        ->get()
                                        ->max('daily_total') ?? 360;
                                        
                        $astroStats = [
                            'worked_hours' => floor($totalWorked / 60) . 'h ' . round($totalWorked % 60) . 'm',
                            'approved_hours' => floor($totalApproved / 60) . 'h ' . round($totalApproved % 60) . 'm',
                            'review_hours' => floor($totalReview / 60) . 'h ' . round($totalReview % 60) . 'm',
                            'rejected_hours' => floor($totalRejected / 60) . 'h ' . round($totalRejected % 60) . 'm',
                            'approval_rate' => $approvalRate,
                            'total_worked' => $totalWorked,
                            'today_worked' => $todayWorked,
                            'highest_daily_minutes' => $highestDailyMinutes,
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
                            'highest_daily_minutes' => 360,
                        ];
                    }
                }

                return view('dashboard.worker', compact('partner', 'metrics', 'reports', 'isRankOneThisWeek', 'userRank', 'isAstro', 'astroStats'));
            }

            if ($partner->isTeamParent()) {
                $metrics = $this->metricsService->getMitraMetrics($partner);
                $userCategory = $this->leaderboardService->resolvePartnerCategory($partner);
                $userRank = $this->leaderboardService->getPartnerWeeklyRank($partner->id, 3, $userCategory);

                return view('dashboard.mitra', compact('partner', 'metrics', 'userRank'));
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

        // Accountant & Tax Manager dedicated financial & tax dashboard
        if ($user->isAccountantTax()) {
            $month = now('Asia/Makassar')->format('Y-m');
            $year = (int) now('Asia/Makassar')->format('Y');
            $report = app(\App\Services\AccountingLedgerService::class)->buildReport($month, $year);
            $taxPeriod = \App\Models\TaxPeriod::firstOrCreate(['period_month' => $month]);
            $recentCycles = \App\Models\PaymentCycle::orderByDesc('id')->limit(5)->get();
            $missingTaxPartnersCount = \App\Models\Partner::query()
                ->where(function ($q): void {
                    $q->whereNull('country_code')->orWhere('country_code', 'ID');
                })
                ->where('is_tax_exempt', false)
                ->where(function ($q): void {
                    $q->whereNull('npwp')->orWhere('npwp', '');
                })
                ->where(function ($q): void {
                    $q->whereNull('nik')->orWhere('nik', '');
                })
                ->count();

            return view('dashboard.accountant-tax', compact(
                'user',
                'month',
                'report',
                'taxPeriod',
                'recentCycles',
                'missingTaxPartnersCount'
            ));
        }

        // Check if user is an internal admin/finance user
        if ($user->hasFullAdminAccess() || $user->role === 'finance') {
            $metrics = $this->metricsService->getGlobalMetrics();
            
            $latestReports = \App\Models\AtlasTask::with(['atlasWorker'])
                ->orderBy('task_date', 'desc')
                ->limit(10)
                ->get();

            $clientInvoices = \App\Models\Invoice::with('client')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $dashboardCache = app(\App\Services\DashboardCacheService::class);

            $monthlyData = collect($dashboardCache->remember('admin_monthly_data_v3', \App\Services\DashboardCacheService::TTL_CHARTS, function () {
                $isMysql = \Illuminate\Support\Facades\DB::getDriverName() === 'mysql';
                $groupByRawTaskDate = $isMysql ? "DATE_FORMAT(task_date, '%Y-%m')" : "strftime('%Y-%m', task_date)";
                
                $approvedData = \App\Models\AtlasTask::select(
                        \Illuminate\Support\Facades\DB::raw("$groupByRawTaskDate as month"),
                        \Illuminate\Support\Facades\DB::raw("SUM(approved_minutes) as approved_minutes")
                    )
                    ->where('task_date', '>=', now()->subMonths(6)->startOfMonth())
                    ->groupBy('month')
                    ->get()
                    ->keyBy('month')
                    ->toArray();

                $submittedData = \App\Models\AtlasTask::select(
                        \Illuminate\Support\Facades\DB::raw("$groupByRawTaskDate as month"),
                        \Illuminate\Support\Facades\DB::raw("SUM(worked_minutes) as submitted_minutes")
                    )
                    ->where('task_date', '>=', now()->subMonths(6)->startOfMonth())
                    ->groupBy('month')
                    ->get()
                    ->keyBy('month')
                    ->toArray();
                
                $merged = [];
                $months = collect(array_keys($approvedData))->merge(array_keys($submittedData))->unique()->sort()->values();
                foreach ($months as $month) {
                    $merged[] = [
                        'month' => $month,
                        'approved_minutes' => $approvedData[$month]['approved_minutes'] ?? 0,
                        'submitted_minutes' => $submittedData[$month]['submitted_minutes'] ?? 0,
                    ];
                }
                return $merged;
            }));

            $dailyAverageData = collect($dashboardCache->remember('admin_daily_total_data_v1', \App\Services\DashboardCacheService::TTL_CHARTS, function () {
                return \App\Models\AtlasTask::select(
                        \Illuminate\Support\Facades\DB::raw('task_date as submission_date'),
                        \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(worked_minutes), 0) as total_minutes')
                    )
                    ->where('task_date', '>=', now('Asia/Makassar')->subDays(7)->toDateString())
                    ->groupBy('task_date')
                    ->orderBy('task_date', 'asc')
                    ->get()
                    ->map(fn ($row) => [
                        'submission_date' => $row->submission_date,
                        'total_minutes' => (float) $row->total_minutes,
                    ])
                    ->toArray();
            }));

            return view('dashboard.admin', compact('metrics', 'latestReports', 'clientInvoices', 'monthlyData', 'dailyAverageData'));
        }

        // Default fallback dashboard
        return view('dashboard.fallback', compact('user'));
    }
}
