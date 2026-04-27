<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedTime extends Model
{
    protected $fillable = [
    'business_id',
    'blocked_date',
    'day_of_week',
    'is_recurring',
    'start_time',
    'end_time',
    'full_day',
    'reason',
];
}
