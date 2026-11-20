<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpServerController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileRecordingController;
use App\Http\Controllers\Api\PrivateStudioPartnerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('private/studio')->middleware([
    'throttle:30,1',
    \App\Http\Middleware\AuthenticateStudioIntegration::class,
])->name('api.private.studio.')->group(function () {
    Route::get('/parents', [PrivateStudioPartnerController::class, 'parents'])->name('parents');
    Route::get('/partners', [PrivateStudioPartnerController::class, 'index'])->name('partners.show');
    Route::get('/partners/{partner}', [PrivateStudioPartnerController::class, 'show'])->name('partners.detail');
    Route::post('/partners', [PrivateStudioPartnerController::class, 'store'])->name('partners.store');
    Route::patch('/partners/{partner}/status', [PrivateStudioPartnerController::class, 'setStatus'])->name('partners.status');
    Route::put('/partners/{partner}', [PrivateStudioPartnerController::class, 'update'])->name('partners.update');
    Route::delete('/partners/{partner}', [PrivateStudioPartnerController::class, 'destroy'])->name('partners.destroy');
});

// MCP Server Endpoint
Route::post('/mcp', [McpServerController::class, 'handle'])
    ->middleware([
        'throttle:60,1', 
        \App\Http\Middleware\McpAuthenticate::class,
        \App\Http\Middleware\McpAuditLogger::class,
    ])
    ->name('api.mcp.handle');


// ==========================================
// MOBILE APP ENDPOINTS (Phase 2)
// ==========================================
Route::prefix('mobile')->group(function () {
    
    // Auth Endpoint (No Middleware)
    Route::post('/login', [MobileAuthController::class, 'login']);

    // Protected Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        // Metadata & Categories
        Route::get('/categories', [MobileRecordingController::class, 'getCategories']);
        
        // Recording Flow (Direct Local Upload)
        Route::post('/recordings/upload', [MobileRecordingController::class, 'upload']);
    });
});

// ==========================================
// ATLAS BOT WEBHOOK
// ==========================================
Route::post('/bot/atlas/sync', [\App\Http\Controllers\Api\AtlasBotController::class, 'sync']);
Route::post('/bot/1618/sync', [\App\Http\Controllers\Api\AtlasBotController::class, 'sync1618'])->middleware('throttle:60,1');
