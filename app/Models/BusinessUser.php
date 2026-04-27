<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BusinessUser extends Authenticatable
{
    protected $fillable = [
        'business_id',
        'first_name',
        'last_name',
        'phone',
        'password',
        'is_owner',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}