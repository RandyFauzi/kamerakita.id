<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoboticsSyncRun extends Model
{
    protected $guarded = [];

    protected $casts = ['result' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
}
