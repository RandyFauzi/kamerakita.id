<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class EvidenceFileBackup extends Model
{
    protected $fillable = [
        'path',
        'mime_type',
        'file_size',
        'contents',
    ];

    public function decodedContents(): string
    {
        $decoded = base64_decode($this->contents, true);

        if ($decoded === false) {
            throw new RuntimeException("Cadangan evidence rusak: {$this->path}");
        }

        return $decoded;
    }
}
