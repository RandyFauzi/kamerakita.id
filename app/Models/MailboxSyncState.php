<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxSyncState extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_account',
        'folder_name',
        'uidvalidity',
        'last_uid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
