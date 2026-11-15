<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceEntry extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = ['amount' => 'integer', 'entry_date' => 'date'];
}
