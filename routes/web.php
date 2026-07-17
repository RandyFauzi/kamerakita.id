<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmitVideoWorkReportController;
use App\Http\Controllers\VerifyVideoWorkReportController;
use App\Http\Controllers\RenderDashboardOverviewController;
use App\Http\Controllers\ExportPayrollDataController;
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
    Route::resource('partners', ManagePartnerDemographicsController::class);

    // Phase 2: Evidence-Based Submission Module
    Route::get('/submit-report', [SubmitVideoWorkReportController::class, 'create'])->name('video-submissions.submit-report.create');
    Route::post('/submit-report', [SubmitVideoWorkReportController::class, 'store'])->name('video-submissions.submit-report.store');
    
    // Phase 3: QC Video Room
    Route::get('/qc-room', [VerifyVideoWorkReportController::class, 'index'])->name('video-submissions.qc-room');
    Route::post('/qc-room/{report}/verify', [VerifyVideoWorkReportController::class, 'verify'])->name('video-submissions.verify');

    // Phase 5: Bulk Payroll Export Module
    Route::get('/payroll/export-csv', [ExportPayrollDataController::class, 'exportCsv'])->name('payroll.export-csv');
    Route::post('/payroll/mark-as-paid', [ExportPayrollDataController::class, 'markAsPaid'])->name('payroll.mark-as-paid');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
