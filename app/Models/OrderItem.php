<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $casts=['pos_exact_price'=>'decimal:4','pos_exact_total'=>'decimal:4','pos_contains_qty'=>'decimal:6'];
    public function getUnitPriceAttribute($value) { return $this->getAttribute('pos_exact_price') ?? $value; }
    public function getTotalPriceAttribute($value) { return $this->getAttribute('pos_exact_total') ?? $value; }
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'item_name',
        'unit_price',
        'quantity',
        'total_price',
        'notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
