<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EditRejectedVideoWorkReportController;
use App\Http\Controllers\ListPartnerReportHistoryController;
use App\Http\Controllers\ListPartnerPaymentHistoryController;
use App\Http\Controllers\ShowVideoWorkReportEvidenceController;
use App\Http\Controllers\VerifyVideoWorkReportController;
use App\Http\Controllers\RenderDashboardOverviewController;
use App\Http\Controllers\ExportPayrollDataController;
use App\Http\Controllers\ManageAdminUsersController;
use App\Http\Controllers\ManagePartnerDemographicsController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/jadi-vendor', function () {
    return view('jadi-vendor');
})->name('jadi-vendor');

// Main Account (Akun Induk) Application Form
Route::get('/pengajuan-akun-induk', [\App\Http\Controllers\MainAccountRequestController::class, 'create'])
    ->name('main-account.request');
Route::post('/pengajuan-akun-induk', [\App\Http\Controllers\MainAccountRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('main-account.request.store');

Route::get('/portofolio', [\App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio.index');
Route::post('/portofolio/contact', [\App\Http\Controllers\PortfolioController::class, 'inquiry'])
    ->middleware('throttle:10,1')->name('portfolio.inquiry');

Route::get('/onboarding/success', function () {
    return view('onboarding.success');
})->name('onboarding.success');

Route::get('/get-started', [\App\Http\Controllers\OnboardingController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('onboarding.start');
Route::post('/get-started', [\App\Http\Controllers\OnboardingController::class, 'save'])
    ->middleware(['auth', 'verified'])
    ->name('onboarding.save');

Route::get('/dashboard', RenderDashboardOverviewController::class)
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('dashboard');

Route::get('/leaderboard', [\App\Http\Controllers\LeaderboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'onboarding'])
    ->name('leaderboard.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin/finance')->name('admin.finance.')->middleware('role:superadmin,admin,accountant_tax')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('index');
        Route::post('/entries', [\App\Http\Controllers\Admin\FinanceController::class, 'store'])->name('store');
        Route::put('/entries/{entry}', [\App\Http\Controllers\Admin\FinanceController::class, 'update'])->name('update');
        Route::delete('/entries/{entry}', [\App\Http\Controllers\Admin\FinanceController::class, 'destroy'])->name('destroy');
        Route::post('/settings', [\App\Http\Controllers\Admin\FinanceController::class, 'settings'])->name('settings');
        Route::post('/distribute', [\App\Http\Controllers\Admin\FinanceController::class, 'distribute'])->name('distribute');
    });

    // Accountant & Tax Manager: Tax Center & Accounting Reports
    Route::prefix('tax')->name('tax.')->middleware('role:superadmin,admin,accountant_tax')->group(function () {
        Route::get('/', [\App\Http\Controllers\TaxCenterController::class, 'index'])->name('index');
        Route::post('/periods/sync', [\App\Http\Controllers\TaxCenterController::class, 'syncPeriod'])->name('periods.sync');
        Route::post('/periods/{taxPeriod}/status', [\App\Http\Controllers\TaxCenterController::class, 'updatePeriodStatus'])->name('periods.status');
        Route::patch('/records/{taxRecord}', [\App\Http\Controllers\TaxCenterController::class, 'updateRecord'])->name('records.update');
        Route::post('/records/{taxRecord}/bupot', [\App\Http\Controllers\TaxCenterController::class, 'uploadBupot'])->name('records.bupot');
        Route::get('/records/{taxRecord}/bupot/download', [\App\Http\Controllers\TaxCenterController::class, 'downloadBupot'])->name('records.bupot.download');
        Route::post('/partners/{partner}/tax-profile', [\App\Http\Controllers\TaxCenterController::class, 'updatePartnerTaxProfile'])->name('partners.update');
        Route::post('/cycles/{cycle}/post-journal', [\App\Http\Controllers\TaxCenterController::class, 'postCycleToJournal'])->name('post-cycle');
        Route::get('/export-csv', [\App\Http\Controllers\TaxCenterController::class, 'exportCoretaxCsv'])->name('export-csv');
    });

    Route::prefix('accounting')->name('accounting.')->middleware('role:superadmin,admin,accountant_tax')->group(function () {
        Route::get('/reports', [\App\Http\Controllers\AccountingReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-csv', [\App\Http\Controllers\AccountingReportController::class, 'exportCsv'])->name('reports.export-csv');
    });

    Route::get('/video-work-reports/{report}/evidence/{type}', ShowVideoWorkReportEvidenceController::class)
        ->middleware(['signed:relative'])
        ->name('video-submissions.evidence.show');

    // Phase 1: Partner Demographics (Read + Export for Accountant & Tax audit; Full CRUD for Superadmin/Admin)
    Route::get('/partners/export', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'export'])
        ->middleware('role:superadmin,admin,accountant_tax')
        ->name('partners.export');
    Route::get('/partners/export-contacts', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'exportContacts'])
        ->middleware('role:superadmin,admin,accountant_tax')
        ->name('partners.export-contacts');
    Route::get('/partners', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'index'])
        ->middleware('role:superadmin,admin,accountant_tax')
        ->name('partners.index');
    Route::post('/partners/bulk-update', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'bulkUpdate'])
        ->middleware('role:superadmin,admin')
        ->name('partners.bulk-update');
    Route::post('/partners/{partner}/toggle-bank-bypass', [\App\Http\Controllers\ManagePartnerDemographicsController::class, 'toggleWorkerBankBypass'])
        ->middleware('role:superadmin,admin')
        ->name('partners.toggle-bank-bypass');
    Route::resource('partners', ManagePartnerDemographicsController::class)
        ->except(['index', 'show'])
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

    // Admin Password Recovery Management
    Route::get('/admin/password-recoveries', [\App\Http\Controllers\Admin\PasswordRecoveryController::class, 'index'])
        ->middleware('role:superadmin,admin')
        ->name('admin.password-recoveries.index');
    Route::post('/admin/password-recoveries/{recoveryRequest}/approve', [\App\Http\Controllers\Admin\PasswordRecoveryController::class, 'approve'])
        ->middleware('role:superadmin,admin')
        ->name('admin.password-recoveries.approve');
    Route::post('/admin/password-recoveries/{recoveryRequest}/reject', [\App\Http\Controllers\Admin\PasswordRecoveryController::class, 'reject'])
        ->middleware('role:superadmin,admin')
        ->name('admin.password-recoveries.reject');
    Route::post('/admin/password-recoveries/{recoveryRequest}/block', [\App\Http\Controllers\Admin\PasswordRecoveryController::class, 'block'])
        ->middleware('role:superadmin,admin')
        ->name('admin.password-recoveries.block');
    Route::post('/admin/password-recoveries/{recoveryRequest}/send-wa', [\App\Http\Controllers\Admin\PasswordRecoveryController::class, 'sendWhatsApp'])
        ->middleware('role:superadmin,admin')
        ->name('admin.password-recoveries.send-wa');

    // Admin Main Account Requests Management
    Route::prefix('admin/main-account-requests')->name('admin.main-account-requests.')->middleware('role:superadmin,admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MainAccountRequestController::class, 'index'])->name('index');
        Route::post('/{mainAccountRequest}/approve', [\App\Http\Controllers\Admin\MainAccountRequestController::class, 'approve'])->name('approve');
        Route::post('/{mainAccountRequest}/reject', [\App\Http\Controllers\Admin\MainAccountRequestController::class, 'reject'])->name('reject');
    });

    Route::post('/leaderboard/reset', [\App\Http\Controllers\LeaderboardController::class, 'reset'])
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
    Route::get('/kirim-laporan', [\App\Http\Controllers\FilePondReportUploadController::class, 'create'])->name('reports.create');
    Route::post('/kirim-laporan', [\App\Http\Controllers\FilePondReportUploadController::class, 'store'])->name('reports.store');
    Route::post('/kirim-laporan/temp', [\App\Http\Controllers\FilePondReportUploadController::class, 'processTemp'])->name('reports.temp.process');
    Route::delete('/kirim-laporan/temp', [\App\Http\Controllers\FilePondReportUploadController::class, 'revertTemp'])->name('reports.temp.revert');
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
    Route::post('/qc-room/quick-reconcile', [VerifyVideoWorkReportController::class, 'quickReconcileByEmail'])
        ->middleware('role:superadmin,admin')
        ->name('video-submissions.quick-reconcile');
    // Payment Cycle & Pembayaran Module (Based on Excel Upload)
    Route::prefix('payments/manage')->name('payments.')->middleware('role:superadmin,admin,finance,accountant_tax')->group(function () {
        Route::get('/', [\App\Http\Controllers\ManagePaymentsController::class, 'index'])->name('manage');
        Route::post('/import-csv', [\App\Http\Controllers\ManagePaymentsController::class, 'importCsv'])->name('import-csv');
        Route::put('/cycles/{cycle}/financials', [\App\Http\Controllers\ManagePaymentsController::class, 'updateCycleFinancials'])->name('update-financials');
        Route::post('/cycles/{cycle}/rates', [\App\Http\Controllers\ManagePaymentsController::class, 'updateRates'])->name('update-rates');
        Route::delete('/cycles/{cycle}', [\App\Http\Controllers\ManagePaymentsController::class, 'destroyCycle'])->name('destroy-cycle');
        Route::post('/entries/status', [\App\Http\Controllers\ManagePaymentsController::class, 'updatePaymentStatus'])->name('update-status');
        Route::get('/export-csv', [\App\Http\Controllers\ManagePaymentsController::class, 'exportCsv'])->name('export-csv');
    });

    // Phase 5: Bulk Payroll Export Module
    Route::get('/payroll/export-csv', [ExportPayrollDataController::class, 'exportCsv'])
        ->middleware('role:superadmin,admin,finance,accountant_tax')
        ->name('payroll.export-csv');
    Route::get('/payroll/export-hourly-tracker-excel', [ExportPayrollDataController::class, 'exportHourlyTrackerExcel'])
        ->middleware('role:superadmin,admin,finance,accountant_tax')
        ->name('payroll.export-hourly-tracker-excel');
    Route::post('/payroll/mark-as-paid', [ExportPayrollDataController::class, 'markAsPaid'])
        ->middleware('role:superadmin,admin,finance,accountant_tax')
        ->name('payroll.mark-as-paid');

    // Web Push Subscriptions
    Route::post('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'update'])
        ->name('push-subscriptions.update');
    Route::delete('/push-subscriptions', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])
        ->name('push-subscriptions.destroy');

    // Push Notification Broadcaster
    Route::get('/push-notifications/broadcast', [\App\Http\Controllers\Admin\PushNotificationController::class, 'index'])
        ->middleware('role:superadmin,admin')
        ->name('push-notifications.broadcast.index');
    Route::post('/push-notifications/broadcast', [\App\Http\Controllers\Admin\PushNotificationController::class, 'send'])
        ->middleware('role:superadmin,admin')
        ->name('admin.push-notifications.send');

    // AI Admin Command Center Endpoint
    Route::post('/admin-assistant', [\App\Http\Controllers\Api\AdminAssistantController::class, 'handle'])
        ->middleware('role:superadmin,admin')
        ->name('admin-assistant');

    // MCP Integrations Management
    Route::prefix('admin/integrations/mcp')->name('admin.integrations.mcp.')->middleware('role:superadmin,admin,finance')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\McpConnectionController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\McpConnectionController::class, 'store'])->name('store');
        Route::post('/{connection}/reveal', [\App\Http\Controllers\Admin\McpConnectionController::class, 'reveal'])->name('reveal');
        Route::delete('/{connection}', [\App\Http\Controllers\Admin\McpConnectionController::class, 'destroy'])->name('destroy');
        Route::delete('/oauth/{tokenId}', [\App\Http\Controllers\Admin\McpConnectionController::class, 'revokeOAuth'])->name('oauth.revoke');
    });
});

Route::middleware(['auth'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/recordings', [\App\Http\Controllers\VendorRecordingController::class, 'index'])->name('recordings.index');
    Route::get('/reports', [\App\Http\Controllers\VendorController::class, 'qcTracker'])->name('reports.index');
    Route::get('/payments', [\App\Http\Controllers\VendorController::class, 'payments'])->name('payments.index');
    Route::get('/workers', [\App\Http\Controllers\VendorController::class, 'workers'])->name('workers.index');
    Route::post('/workers', [\App\Http\Controllers\VendorController::class, 'storeWorker'])->name('workers.store');
    Route::post('/workers/toggle-bank-bypass', [\App\Http\Controllers\VendorController::class, 'toggleWorkerBankBypass'])->name('workers.toggle-bank-bypass');
    Route::put('/workers/{id}', [\App\Http\Controllers\VendorController::class, 'updateWorker'])->name('workers.update');
});

Route::middleware('auth')->group(function () {
    // Superadmin Account POV Switcher (strictly authorized inside controller/service for randyfauzi24@gmail.com)
    Route::prefix('account-pov')->name('account-pov.')->group(function () {
        Route::post('/enter/{user}', [\App\Http\Controllers\AccountPovController::class, 'enter'])
            ->middleware('throttle:30,1')
            ->name('enter');
        Route::post('/leave', [\App\Http\Controllers\AccountPovController::class, 'leave'])
            ->name('leave');
    });

    // Mailbox Internal (UI)
    Route::get('/mailbox', [App\Http\Controllers\MailboxController::class, 'index'])->name('mailbox.index');
    
    // Mailbox API (Throttled for abuse protection)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/mailbox/api/emails', [App\Http\Controllers\MailboxController::class, 'fetchEmails'])->name('mailbox.api.emails');
        Route::get('/mailbox/api/emails/{email}', [App\Http\Controllers\MailboxController::class, 'showEmail'])->name('mailbox.api.email.show');
        Route::patch('/mailbox/{email}/read', [App\Http\Controllers\MailboxController::class, 'toggleRead'])->name('mailbox.toggle-read');
        Route::patch('/mailbox/{email}/star', [App\Http\Controllers\MailboxController::class, 'toggleStarred'])->name('mailbox.toggle-star');
        Route::delete('/mailbox/{email}', [App\Http\Controllers\MailboxController::class, 'destroy'])->name('mailbox.destroy');
    });

    // Panduan Book
    Route::get('/panduan', function () {
        return view('panduan.index');
    })->name('panduan.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/avatar/{path}', [\App\Http\Controllers\ShowAvatarController::class, '__invoke'])->where('path', '.*')->name('avatar.show');

    // Invoices (Protected for internal financial roles)
    Route::middleware('role:superadmin,admin,finance,accountant_tax')->group(function () {
        Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.create');
        Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/preview', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('/invoices/{invoice}/issue', [\App\Http\Controllers\InvoiceController::class, 'issue'])->name('invoices.issue');
        Route::post('/invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::post('/invoices/{invoice}/void', [\App\Http\Controllers\InvoiceController::class, 'voidInvoice'])->name('invoices.void');
    });
});

// Public Fastwork Onboarding routes
Route::get('/onboarding', [\App\Http\Controllers\FastworkOnboardingController::class, 'showForm'])->name('onboarding.form');
Route::get('/onboarding/register', [\App\Http\Controllers\FastworkOnboardingController::class, 'showRegisterForm'])->name('onboarding.register');
Route::post('/onboarding', [\App\Http\Controllers\FastworkOnboardingController::class, 'handleSubmission'])->name('onboarding.submit');

require __DIR__.'/auth.php';

    Route::get('/tasks', [\App\Http\Controllers\MasterTaskController::class, 'index'])->name('tasks.index');
    Route::get('/admin/recordings', [\App\Http\Controllers\Admin\AtlasRecordingController::class, 'index'])->middleware('role:superadmin,admin')->name('admin.recordings.index');
    Route::post('/admin/recordings/refresh-1618', [\App\Http\Controllers\Admin\AtlasRecordingController::class, 'refresh1618'])->middleware(['role:superadmin,admin', 'throttle:6,1'])->name('admin.recordings.refresh1618');
    Route::get('/admin/recordings/sync-status', [\App\Http\Controllers\Admin\AtlasRecordingController::class, 'syncStatus'])->middleware(['role:superadmin,admin', 'throttle:30,1'])->name('admin.recordings.sync-status');

    Route::get('/astro/recording', [\App\Http\Controllers\Astro\AstroRecordingController::class, 'index'])->name('astro.recording');
