<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosSubscription extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime', 'device_limit' => 'integer', 'license_version' => 'integer', 'primary_device_id' => 'integer'];
    public function business() { return $this->belongsTo(Business::class); }
    public function getIsValidAttribute(): bool { return $this->status === 'active' && $this->ends_at && now()->lt($this->ends_at); }
}
