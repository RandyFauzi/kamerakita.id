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
        'received_at',
        'is_read',
        'is_starred',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
