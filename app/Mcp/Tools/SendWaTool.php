<?php

namespace App\Mcp\Tools;

use App\Services\HandcapWaService;

class SendWaTool extends BaseTool
{
    public function getName(): string
    {
        return 'send_wa';
    }

    public function getDescription(): string
    {
        return 'Kirim pesan teks WhatsApp instan ke nomor HP (otomatis diubah ke kode negara).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'phone' => ['type' => 'string', 'description' => 'Nomor HP tujuan (misal: 089536...)'],
                'message' => ['type' => 'string', 'description' => 'Isi pesan teks yang ingin dikirimkan']
            ],
            'required' => ['phone', 'message']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.critical';
    }

    public function execute(array $args, array $client)
    {
        $phone = $args['phone'] ?? null;
        $message = $args['message'] ?? null;

        if (!$phone || !$message) {
            throw new \Exception("Parameter phone dan message wajib diisi.");
        }

        $waService = new HandcapWaService();
        $response = $waService->sendMessage($phone, $message, 'high'); // Use high priority

        return [
            'status' => 'success',
            'message' => 'Pesan WA berhasil dikirim',
            'gateway_response' => $response
        ];
    }
}
