<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'mode',
        'is_active',
    ];

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    
}