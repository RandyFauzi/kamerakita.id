<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'target',
        'scheduled_at',
        'total_targets',
        'delivered',
        'failed',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function deliveries()
    {
        return $this->hasMany(NotificationDelivery::class, 'campaign_id');
    }
}
