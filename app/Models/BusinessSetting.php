<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
   protected $fillable = [
    'business_id',
    'slot_interval',
    'max_days_ahead',
    'currency',
    'timezone',
    'whatsapp_number',
    'booking_enabled',
    'ordering_enabled',
    'public_tagline',
    'public_theme',
    'primary_color',
    'secondary_color',
    'background_color',
    'text_color',
    'card_color',
    'button_color',
    'font_family',
    'border_radius',
    'logo',
    'group_services_on_public_page',
];
}
