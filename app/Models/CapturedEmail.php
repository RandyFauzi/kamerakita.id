<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapturedEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_address',
        'subject',
        'message_content',
        'imap_uid',
        'imap_uidvalidity',
        'message_id',
        'received_at',
        'is_read',
        'is_starred',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
    ];

    public function getSanitizedContentAttribute()
    {
        if (!$this->message_content) {
            return '';
        }
        return clean($this->message_content);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
