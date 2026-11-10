<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosDevice extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['token_hash'];
    protected $casts = ['last_verified_at' => 'immutable_datetime', 'revoked_at' => 'immutable_datetime'];
    public function business() { return $this->belongsTo(Business::class); }
}
