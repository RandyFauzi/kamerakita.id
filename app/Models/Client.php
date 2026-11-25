<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'address',
        'tax_id',
        'is_domestic',
        'default_currency',
        'default_rate',
    ];

    protected $casts = [
        'is_domestic' => 'boolean',
        'default_rate' => 'decimal:2',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
