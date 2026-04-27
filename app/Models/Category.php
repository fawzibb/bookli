<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'sort_order',
        'is_active',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}