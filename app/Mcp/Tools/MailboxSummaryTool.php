<?php

namespace App\Mcp\Tools;

use App\Models\CapturedEmail;
use App\Models\MailboxSyncState;

class MailboxSummaryTool extends BaseTool
{
    public function getName(): string
    {
        return 'mailbox_summary';
    }

    public function getDescription(): string
    {
        return 'Ringkasan Mailbox IMAP: jumlah email masuk, belum dibaca, status sinkronisasi mailbox terakhir, dan daftar subjek email terbaru.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'is_read' => ['type' => 'boolean', 'description' => 'Filter status baca (true/false)'],
                'keyword' => ['type' => 'string', 'description' => 'Cari pengirim atau subjek email'],
                'limit' => ['type' => 'integer', 'description' => 'Jumlah email terbaru (default 15, maks 50)'],
            ],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $limit = min(max((int) ($args['limit'] ?? 15), 1), 50);

        $query = CapturedEmail::query();

        if (array_key_exists('is_read', $args)) {
            $query->where('is_read', (bool) $args['is_read']);
        }
        if (! empty($args['keyword'])) {
            $kw = $args['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('sender_address', 'LIKE', "%{$kw}%")
                    ->orWhere('subject', 'LIKE', "%{$kw}%");
            });
        }

        return [
            'summary' => [
                'total_emails' => CapturedEmail::count(),
                'unread_emails' => CapturedEmail::where('is_read', false)->count(),
                'starred_emails' => CapturedEmail::where('is_starred', true)->count(),
            ],
            'sync_states' => MailboxSyncState::query()->latest('updated_at')->limit(10)->get(),
            'latest_emails' => $query->latest('received_at')->limit($limit)->get([
                'id', 'user_id', 'sender_address', 'subject', 'imap_account', 'received_at', 'is_read', 'is_starred',
            ]),
        ];
    }
}
