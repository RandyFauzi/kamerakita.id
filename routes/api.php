<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpServerController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileRecordingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
