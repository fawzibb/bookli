<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceGroup;

class Service extends Model
{
    protected $fillable = [
    'business_id',
    'name',
    'description',
    'price',
    'duration',
    'is_active',
    'service_group_id',
];

public function serviceGroup()
{
    return $this->belongsTo(ServiceGroup::class);
}

}
