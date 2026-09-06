<?php

namespace App\Mcp\Tools;

use App\Mcp\Contracts\McpToolInterface;

abstract class BaseTool implements McpToolInterface
{
    /**
     * Helper to mask sensitive data from collections
     */
    protected function maskSensitiveData($collection)
    {
        if ($collection instanceof \Illuminate\Support\Collection) {
            $collection->each(function ($item) {
                if ($item instanceof \App\Models\Partner) {
                    $item->makeHidden(['nik', 'full_address', 'whatsapp_number', 'bank_account_number']);
                }
                if ($item instanceof \App\Models\User && $item->partner) {
                    $item->partner->makeHidden(['nik', 'full_address', 'whatsapp_number', 'bank_account_number']);
                }
            });
        }
        return $collection;
    }

    protected function getQueryForResource($resource)
    {
        switch ($resource) {
            case 'users': return \App\Models\User::query();
            case 'partners': return \App\Models\Partner::query();
            case 'video_work_reports': return \App\Models\VideoWorkReport::query();
            case 'captured_emails': return \App\Models\CapturedEmail::query();
            case 'mailbox_sync_states': return \App\Models\MailboxSyncState::query();
            default: throw new \Exception("Resource not allowed: {$resource}");
        }
    }

    protected function applyFilters($query, $filters)
    {
        if (!is_array($filters)) return;
        foreach ($filters as $column => $condition) {
            if (is_array($condition)) {
                if (isset($condition['in'])) {
                    $query->whereIn($column, $condition['in']);
                }
                if (isset($condition['between']) && is_array($condition['between']) && count($condition['between']) === 2) {
                    $query->whereBetween($column, $condition['between']);
                }
                if (isset($condition['>'])) $query->where($column, '>', $condition['>']);
                if (isset($condition['<'])) $query->where($column, '<', $condition['<']);
                if (isset($condition['>='])) $query->where($column, '>=', $condition['>=']);
                if (isset($condition['<='])) $query->where($column, '<=', $condition['<=']);
                if (isset($condition['!='])) $query->where($column, '!=', $condition['!=']);
                if (isset($condition['like'])) $query->where($column, 'LIKE', $condition['like']);
            } else {
                $query->where($column, $condition);
            }
        }
    }
}
