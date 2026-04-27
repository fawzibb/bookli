<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationCode extends Model
{
    protected $fillable = [
        'code',
        'days',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

public function redemptions()
{
    return $this->hasMany(ActivationCodeRedemption::class);
}

}