<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpServerController;
use App\Http\Controllers\Api\AdminAssistantController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MCP Server Endpoint
Route::post('/mcp', [McpServerController::class, 'handle'])
    ->name('api.mcp.handle');

// AI Admin Command Center Endpoint
Route::post('/admin-assistant', [AdminAssistantController::class, 'handle'])
    ->middleware('auth:sanctum')
    ->name('api.admin-assistant');
