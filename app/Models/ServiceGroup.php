<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'capacity_per_slot',
        'is_active',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}