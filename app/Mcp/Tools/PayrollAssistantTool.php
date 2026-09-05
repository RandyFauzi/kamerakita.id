<?php

namespace App\Mcp\Tools;

use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\DB;

class PayrollAssistantTool extends BaseTool
{
    public function getName(): string
    {
        return 'payroll_assistant';
    }

    public function getDescription(): string
    {
        return 'Bantu operasi payroll: baca tagihan berjalan, atau tandai tagihan lunas.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => ['type' => 'string', 'description' => 'read_stats atau mark_paid']
            ],
            'required' => ['action']
        ];
    }

    public function getRequiredPermission(): string
    {
        // Require write since it can mutate, although 'read_stats' is harmless
        return 'mcp.write';
    }

    public function execute(array $args, array $client)
    {
        $action = $args['action'] ?? 'read_stats'; 
        
        if ($action === 'read_stats') {
            $unpaidApproved = VideoWorkReport::where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->selectRaw('SUM(approved_duration_minutes) as total_minutes')
                ->first();
            
            return [
                'total_unpaid_approved_minutes' => $unpaidApproved->total_minutes ?? 0,
                'message' => 'Ringkasan tagihan berjalan.'
            ];
        }

        if ($action === 'mark_paid') {
            $updated = VideoWorkReport::where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'verifier_notes' => DB::raw("CONCAT(COALESCE(verifier_notes, ''), ' | (MCP Bot) Marked Paid')")
                ]);

            return ['message' => "Berhasil menandai {$updated} laporan menjadi Paid."];
        }

        throw new \Exception("Unknown payroll action: {$action}");
    }
}
