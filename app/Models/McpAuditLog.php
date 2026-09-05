<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McpAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'method',
        'tool_name',
        'payload',
        'status',
        'error_message',
        'execution_time_ms',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'execution_time_ms' => 'decimal:2',
    ];
}
