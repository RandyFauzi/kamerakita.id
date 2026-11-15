<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancePeriod extends Model
{
    protected $guarded = [];
    protected $casts = ['closed_at' => 'datetime', 'distribution' => 'array'];
}
