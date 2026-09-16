<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailboxUnmatchedEmail extends Model
{
    protected $fillable = [
        'email_account',
        'imap_uid',
        'imap_uidvalidity',
        'recipient',
        'sender',
        'subject',
        'message_id',
        'received_at',
        'reason'
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];
}
