<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ClientInvoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_month',
        'total_minutes_billed',
        'total_amount_usd',
        'status',
    ];
}
