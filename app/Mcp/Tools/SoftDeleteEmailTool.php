<?php

namespace App\Mcp\Tools;

use App\Models\CapturedEmail;

class SoftDeleteEmailTool extends BaseTool
{
    public function getName(): string
    {
        return 'soft_delete_email';
    }

    public function getDescription(): string
    {
        return 'Sembunyikan email (Soft Delete).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email_id' => ['type' => 'integer']
            ],
            'required' => ['email_id']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.critical';
    }

    public function execute(array $args, array $client)
    {
        $id = $args['email_id'] ?? null;
        $email = CapturedEmail::find($id);
        
        if ($email) {
            $email->delete(); 
            return ['message' => "Email {$id} disembunyikan (SoftDelete)."];
        }
        
        return ['message' => 'Email tidak ditemukan.'];
    }
}
