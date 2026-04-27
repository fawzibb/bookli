<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}