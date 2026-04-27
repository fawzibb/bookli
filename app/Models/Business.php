<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'business_type',
        'mode',
        'phone',
        'country',
        'city',
        'address',
        'logo',
        'is_active',
        'business_type_id',
        'capacity_per_slot',
    ];

    public function users()
    {
        return $this->hasMany(BusinessUser::class);
    }
    public function businessType()
{
    return $this->belongsTo(\App\Models\BusinessType::class);
}

    public function settings()
    {
        return $this->hasOne(BusinessSetting::class);
    }
    public function redemptions()
{
    return $this->hasMany(ActivationCodeRedemption::class);
}

public function services()
{
    return $this->hasMany(Service::class);
}

public function bookings()
{
    return $this->hasMany(Booking::class);
}

public function categories()
{
    return $this->hasMany(Category::class);
}

public function menuItems()
{
    return $this->hasMany(MenuItem::class);
}

public function orders()
{
    return $this->hasMany(Order::class);
}

public function schedules()
{
    return $this->hasMany(Schedule::class);
}

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}