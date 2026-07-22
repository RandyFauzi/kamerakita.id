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

    public function __construct(CalculatePartnerMetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
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

                return view('dashboard.worker', compact('partner', 'metrics', 'reports'));
            }

            if ($partner->partner_role === 'mitra') {
                $metrics = $this->metricsService->getMitraMetrics($partner);

                return view('dashboard.mitra', compact('partner', 'metrics'));
            }
        }

        // Check if user is an internal admin/finance user
        if ($user->hasFullAdminAccess() || $user->role === 'finance') {
            $metrics = $this->metricsService->getGlobalMetrics();
            
            // Get last 10 submissions globally
            $latestReports = VideoWorkReport::with(['partner'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $clientInvoices = \App\Models\ClientInvoice::orderBy('created_at', 'desc')->get();

            return view('dashboard.admin', compact('metrics', 'latestReports', 'clientInvoices'));
        }

        // Default fallback dashboard
        return view('dashboard.fallback', compact('user'));
    }
}
