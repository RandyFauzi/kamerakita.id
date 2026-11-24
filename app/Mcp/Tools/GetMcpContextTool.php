<?php

namespace App\Mcp\Tools;

use App\Services\McpAccess;

class GetMcpContextTool extends BaseTool
{
    public function getName(): string
    {
        return 'get_mcp_context';
    }

    public function getDescription(): string
    {
        return 'Dapatkan konteks pengguna aktif, role, izin MCP, serta peta modul & sumber data Kamerakita (Laporan Manual vs Rekaman Atlas). Panggil tool ini pertama kali jika butuh orientasi sistem.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => (object) [],
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.read';
    }

    public function execute(array $args, array $client)
    {
        $user = request()->user();
        $abilities = app(McpAccess::class)->abilitiesFor($user);

        return [
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? ($client['name'] ?? 'Legacy Client'),
                'email' => $user?->email,
                'role' => $user?->role ?? 'legacy',
                'abilities' => $abilities,
            ],
            'data_pipelines' => [
                'manual_reports' => 'User -> Partner -> VideoWorkReport -> QC (qc_stats, payroll_assistant, batch_approve_reports)',
                'atlas_recordings' => 'Atlas Bot/Sync -> AtlasWorker -> AtlasTask -> Payroll (atlas_recording_summary, payroll_summary)',
                'finance_and_invoices' => 'FinanceEntry & FinancePeriod (finance_summary), Client & Invoice (invoice_summary)',
                'admin_operations' => 'MainAccountRequest, PasswordRecoveryRequest, FastworkOnboarding, ActivityLog (operations_overview)',
            ],
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
        ];
    }
}
