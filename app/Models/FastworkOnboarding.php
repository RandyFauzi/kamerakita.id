<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FastworkOnboarding extends Model
{
    use HasFactory;

    protected $table = 'fastwork_onboardings';

    protected $fillable = [
        'full_name',
        'whatsapp_number',
        'device_type',
        'fastwork_username',
    ];
}
