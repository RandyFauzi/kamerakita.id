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
    return view('welcome');
});

Route::get('/onboarding/success', function () {
    return view('onboarding.success');
})->name('onboarding.success');

Route::get('/get-started', function () {
    return view('get-started');
})->name('get-started');

Route::get('/dashboard', RenderDashboardOverviewController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('leaderboard.index');

Route::get('/video-work-reports/{report}/evidence/{type}', ShowVideoWorkReportEvidenceController::class)
    ->middleware(['signed:relative'])
    ->name('video-submissions.evidence.show');

Route::middleware(['auth', 'verified'])->group(function () {
    // Phase 1: Partner Demographics CRUD
    Route::get('/partners/export-contacts', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'exportContacts'])
        ->middleware('role:superadmin,admin')
        ->name('partners.export-contacts');
    Route::post('/partners/bulk-update', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'bulkUpdate'])
        ->middleware('role:superadmin,admin')
        ->name('partners.bulk-update');
    Route::resource('partners', ManagePartnerDemographicsController::class)
        ->middleware('role:superadmin,admin');

    Route::resource('activation-codes', \App\Http\Controllers\ManageActivationCodesController::class)
        ->except(['create', 'show', 'edit', 'update'])
        ->middleware('role:superadmin,admin');

    Route::get('/admin/activity-logs', \App\Http\Controllers\ListActivityLogsController::class)
        ->middleware('role:superadmin,admin')
        ->name('activity-logs.index');

    // Event Dashboard Route
    Route::get('/admin/event', [\App\Http\Controllers\EventController::class, 'index'])
        ->middleware('role:superadmin,admin')
        ->name('admin.event');

    // Rekruter Management Routes
    Route::get('/rekruter', [\App\Http\Controllers\RekruterController::class, 'index'])
        ->middleware('role:superadmin,admin')
        ->name('rekruter.index');
    Route::get('/rekruter/{rekruter}', [\App\Http\Controllers\RekruterController::class, 'show'])
        ->middleware('role:superadmin,admin')
        ->name('rekruter.show');
    Route::patch('/rekruter/commission/{commission}/pay', [\App\Http\Controllers\RekruterController::class, 'markCommissionPaid'])
        ->middleware('role:superadmin,admin')
        ->name('rekruter.commission.pay');

    // Fastwork Onboardings Admin routes
    Route::get('/admin/onboardings', [\App\Http\Controllers\ManageOnboardingsController::class, 'index'])
        ->middleware('role:superadmin,admin')
        ->name('admin.onboardings.index');
    Route::delete('/admin/onboardings/{onboarding}', [\App\Http\Controllers\ManageOnboardingsController::class, 'destroy'])
        ->middleware('role:superadmin,admin')
        ->name('admin.onboardings.destroy');

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
    Route::post('/qc-room/create-report-by-admin', [VerifyVideoWorkReportController::class, 'storeReportByAdmin'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.create-by-admin');
    Route::get('/qc-room/export-pdf', [VerifyVideoWorkReportController::class, 'exportPdf'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.export-pdf');
    Route::post('/qc-room/save-draft', [VerifyVideoWorkReportController::class, 'saveDraft'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.save-draft');
    Route::post('/qc-room/finalize', [VerifyVideoWorkReportController::class, 'finalizeApproval'])
        ->middleware('role:superadmin,admin,finance')
        ->name('video-submissions.finalize');
    Route::delete('/qc-room/{report}', [VerifyVideoWorkReportController::class, 'destroy'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.destroy');
    Route::post('/qc-room/batch-approve', [VerifyVideoWorkReportController::class, 'batchApprove'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.batch-approve');
    Route::post('/qc-room/{report}/approve', [VerifyVideoWorkReportController::class, 'approveReport'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.approve-report');
    Route::post('/qc-room/{report}/reject', [VerifyVideoWorkReportController::class, 'rejectReport'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.reject-report');
    Route::post('/qc-room/{report}/restore', [VerifyVideoWorkReportController::class, 'restoreReport'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.restore-report');
    Route::post('/qc-room/revert-period', [VerifyVideoWorkReportController::class, 'revertPeriodApproval'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.revert-period');
    // Batch Payment Module
    Route::get('/payments/manage', [\App\Http\Controllers\ManagePaymentsController::class, 'index'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.manage');
    Route::post('/payments/manage/batch-pay', [\App\Http\Controllers\ManagePaymentsController::class, 'batchPay'])
        ->middleware('role:superadmin')
        ->name('payments.batch-pay');
    Route::post('/payments/manage/{partner}/pay', [\App\Http\Controllers\ManagePaymentsController::class, 'processPayment'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.process');
    Route::post('/payments/manage/cancel', [\App\Http\Controllers\ManagePaymentsController::class, 'cancelPayment'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payments.cancel');

    // Phase 5: Bulk Payroll Export Module
    Route::get('/payroll/export-csv', [ExportPayrollDataController::class, 'exportCsv'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payroll.export-csv');
    Route::get('/payroll/export-hourly-tracker-excel', [ExportPayrollDataController::class, 'exportHourlyTrackerExcel'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payroll.export-hourly-tracker-excel');
    Route::post('/payroll/mark-as-paid', [ExportPayrollDataController::class, 'markAsPaid'])
        ->middleware('role:superadmin,admin,finance')
        ->name('payroll.mark-as-paid');

    // Web Push Subscriptions
    Route::post('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'update'])
        ->name('push-subscriptions.update');
    Route::delete('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])
        ->name('push-subscriptions.destroy');

    // AI Admin Command Center Endpoint
    Route::post('/admin-assistant', [\App\Http\Controllers\Api\AdminAssistantController::class, 'handle'])
        ->middleware('role:superadmin,admin')
        ->name('admin-assistant');
});

Route::middleware('auth')->group(function () {
    // Mailbox Internal
    Route::get('/mailbox', [App\Http\Controllers\MailboxController::class, 'index'])->name('mailbox.index');
    Route::patch('/mailbox/{email}/read', [App\Http\Controllers\MailboxController::class, 'toggleRead'])->name('mailbox.toggle-read');
    Route::patch('/mailbox/{email}/star', [App\Http\Controllers\MailboxController::class, 'toggleStarred'])->name('mailbox.toggle-star');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/avatar/{path}', [\App\Http\Controllers\ShowAvatarController::class, '__invoke'])->where('path', '.*')->name('avatar.show');
});

// Public Fastwork Onboarding routes
Route::get('/onboarding', [\App\Http\Controllers\FastworkOnboardingController::class, 'showForm'])->name('onboarding.form');
Route::get('/onboarding/register', [\App\Http\Controllers\FastworkOnboardingController::class, 'showRegisterForm'])->name('onboarding.register');
Route::post('/onboarding', [\App\Http\Controllers\FastworkOnboardingController::class, 'handleSubmission'])->name('onboarding.submit');

require __DIR__.'/auth.php';
