<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivationCode extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'activation_codes';

    protected $fillable = [
        'code',
        'group_name',
    ];
}
