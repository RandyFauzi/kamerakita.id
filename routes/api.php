<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpServerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MCP Server Endpoint
Route::post('/mcp', [McpServerController::class, 'handle'])
    ->name('api.mcp.handle');
