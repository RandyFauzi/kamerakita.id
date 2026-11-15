<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['reserve_bps' => 'integer', 'recipients' => 'array'];
}
