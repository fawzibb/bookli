<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function getTotalAmountAttribute($value) { return $this->getAttribute('pos_total') ?? $value; }
    public function syncEvents() { return $this->hasMany(\App\Models\PosOrderEvent::class); }
    protected $fillable = [
        'business_id',
        'customer_id',
        'order_number',
        'order_type',
        'delivery_fee',
        'delivery_address',
        'status',
        'scheduled_for',
        'total_amount',
        'notes',
        'customer_name',
        'customer_phone',
    ];

    protected $casts = [
        'pos_total'=>'decimal:4','sync_revision'=>'integer','delivered_at'=>'datetime',
        'scheduled_for' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
