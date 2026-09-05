<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RateCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'project',
        'partner_role',
        'effective_from',
        'effective_until',
        'rate_per_hour'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public static function getRate(string $role, string $project = 'atlas', ?Carbon $date = null): float
    {
        $date = $date ?? now();
        
        $rateCard = self::where('partner_role', $role)
            ->where('project', $project)
            ->where('effective_from', '<=', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date->toDateString());
            })
            ->orderBy('effective_from', 'desc')
            ->first();
            
        if ($rateCard) {
            return (float) $rateCard->rate_per_hour;
        }
        
        // Fallbacks if not seeded
        switch ($role) {
            case 'worker': return 50000;
            case 'mitra': return 63000;
            case 'vendor': return 65000;
            case 'commission': return 9000;
        }
        return 0;
    }
}
