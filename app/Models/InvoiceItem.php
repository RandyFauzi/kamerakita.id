<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit',
        'unit_rate',
        'amount',
        'sort_order',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
