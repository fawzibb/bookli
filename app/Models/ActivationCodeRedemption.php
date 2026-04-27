<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivationCodeRedemption extends Model
{
    protected $fillable = [
        'activation_code_id',
        'business_id',
        'redeemed_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function activationCode()
    {
        return $this->belongsTo(ActivationCode::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}