<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosActivationCode extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['code_hash'];
    protected $casts = ['expires_at' => 'immutable_datetime', 'redeemed_at' => 'immutable_datetime', 'is_active' => 'boolean'];
}
