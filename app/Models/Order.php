<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'order_number',
        'order_type',
        'status',
        'scheduled_for',
        'total_amount',
        'notes',
        'customer_name',
        'customer_phone',
    ];

    protected $casts = [
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