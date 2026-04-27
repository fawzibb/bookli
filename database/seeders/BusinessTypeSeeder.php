<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\BusinessType;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // MENU
            ['name' => 'Restaurant', 'mode' => 'menu'],
            ['name' => 'Cafe', 'mode' => 'menu'],
            ['name' => 'Bakery', 'mode' => 'menu'],
            ['name' => 'Sweet Shop', 'mode' => 'menu'],
            ['name' => 'Juice Bar', 'mode' => 'menu'],
            ['name' => 'Fast Food', 'mode' => 'menu'],
            ['name' => 'Pizza Place', 'mode' => 'menu'],
            ['name' => 'Mini Market', 'mode' => 'menu'],

            // BOOKING
            ['name' => 'Barber', 'mode' => 'booking'],
            ['name' => 'Salon', 'mode' => 'booking'],
            ['name' => 'Clinic', 'mode' => 'booking'],
            ['name' => 'Dental Clinic', 'mode' => 'booking'],
            ['name' => 'Spa', 'mode' => 'booking'],
            ['name' => 'Massage Center', 'mode' => 'booking'],
            ['name' => 'Gym', 'mode' => 'booking'],
            ['name' => 'Photography Studio', 'mode' => 'booking'],
            ['name' => 'Car Wash', 'mode' => 'booking'],
            ['name' => 'Repair Shop', 'mode' => 'booking'],
        ];

        foreach ($types as $type) {
            BusinessType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'mode' => $type['mode'],
                    'is_active' => true,
                ]
            );
        }
    }
}