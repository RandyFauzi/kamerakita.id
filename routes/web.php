<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EditRejectedVideoWorkReportController;
use App\Http\Controllers\ListPartnerReportHistoryController;
use App\Http\Controllers\ListPartnerPaymentHistoryController;
use App\Http\Controllers\ShowVideoWorkReportEvidenceController;
use App\Http\Controllers\SubmitVideoWorkReportController;
use App\Http\Controllers\VerifyVideoWorkReportController;
use App\Http\Controllers\RenderDashboardOverviewController;
use App\Http\Controllers\ExportPayrollDataController;
use App\Http\Controllers\ManageAdminUsersController;
use App\Http\Controllers\ManagePartnerDemographicsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', RenderDashboardOverviewController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Phase 1: Partner Demographics CRUD
    Route::resource('partners', ManagePartnerDemographicsController::class)
        ->middleware('role:superadmin,admin');

    // Internal Admin Account CRUD
    Route::resource('admin-users', ManageAdminUsersController::class)
        ->parameters(['admin-users' => 'adminUser'])
        ->except(['show'])
        ->middleware('role:superadmin,admin');

    // Phase 2: Evidence-Based Submission Module
    Route::get('/submit-report', [SubmitVideoWorkReportController::class, 'create'])->name('video-submissions.submit-report.create');
    Route::post('/submit-report', [SubmitVideoWorkReportController::class, 'store'])->name('video-submissions.submit-report.store');
    Route::get('/report-history', ListPartnerReportHistoryController::class)->name('video-submissions.report-history');
    Route::get('/report-history/{report}/edit-rejected', [EditRejectedVideoWorkReportController::class, 'edit'])->name('video-submissions.rejected.edit');
    Route::patch('/report-history/{report}/edit-rejected', [EditRejectedVideoWorkReportController::class, 'update'])->name('video-submissions.rejected.update');
    Route::get('/payment-history', ListPartnerPaymentHistoryController::class)->name('video-submissions.payment-history');
    
    // Phase 3: QC Video Room
    Route::get('/qc-room', [VerifyVideoWorkReportController::class, 'index'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.qc-room');
    Route::get('/qc-room/export-pdf', [VerifyVideoWorkReportController::class, 'exportPdf'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.export-pdf');
    Route::post('/qc-room/{report}/verify', [VerifyVideoWorkReportController::class, 'verify'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.verify');
    Route::delete('/qc-room/{report}', [VerifyVideoWorkReportController::class, 'destroy'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.destroy');
    // Batch Payment Module
    Route::get('/payments/manage', [\App\Http\Controllers\ManagePaymentsController::class, 'index'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.manage');
    Route::post('/payments/manage/{partner}/pay', [\App\Http\Controllers\ManagePaymentsController::class, 'processPayment'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.process');
    Route::post('/payments/manage/cancel', [\App\Http\Controllers\ManagePaymentsController::class, 'cancelPayment'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.cancel');

    Route::get('/video-work-reports/{report}/evidence/{type}', ShowVideoWorkReportEvidenceController::class)
        ->middleware(['role:superadmin,admin,finance,worker,mitra'])
        ->name('video-submissions.evidence.show');

    // Phase 5: Bulk Payroll Export Module
    Route::get('/payroll/export-csv', [ExportPayrollDataController::class, 'exportCsv'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payroll.export-csv');
    Route::post('/payroll/mark-as-paid', [ExportPayrollDataController::class, 'markAsPaid'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payroll.mark-as-paid');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
