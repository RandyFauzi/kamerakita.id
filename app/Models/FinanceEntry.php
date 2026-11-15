<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceEntry extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $attributes = ['revision' => 1];
    protected $casts = ['amount' => 'integer', 'revision' => 'integer', 'entry_date' => 'date'];
}
