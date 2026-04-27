<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'service_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'customer_name',
        'customer_phone',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}