<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvidenceFileBackup extends Model
{
    protected $fillable = [
        'path',
        'mime_type',
        'file_size',
        'contents',
    ];
}
