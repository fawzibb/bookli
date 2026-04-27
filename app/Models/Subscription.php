<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
    'business_id',
    'trial_starts_at',
    'trial_ends_at',
    'subscription_starts_at',
    'subscription_ends_at',
    'status',
];
}
