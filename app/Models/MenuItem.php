<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $casts=['pos_retail_price'=>'decimal:4','pos_contains_qty'=>'decimal:6','pos_available_base'=>'decimal:6','pos_published'=>'boolean'];
    public function getPriceAttribute($value) { return $this->getAttribute('pos_retail_price') ?? $value; }
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
