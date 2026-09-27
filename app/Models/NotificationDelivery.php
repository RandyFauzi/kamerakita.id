<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'channel',
        'status',
        'error_message',
        'attempts',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(NotificationCampaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
