<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    protected $fillable = [
    'business_id',
    'day_of_week',
    'open_time',
    'close_time',
    'is_off',
];
}
