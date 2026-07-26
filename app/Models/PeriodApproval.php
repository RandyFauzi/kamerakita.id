<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodApproval extends Model
{
    use HasFactory;

    protected $table = 'period_approvals';

    protected $fillable = [
        'partner_id',
        'period_start_date',
        'period_end_date',
        'approved_minutes',
        'status',
        'verifier_notes',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'approved_minutes' => 'integer',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
