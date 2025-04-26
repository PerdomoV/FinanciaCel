<?php

namespace Database\Seeders;

use App\Models\Phone;
use Illuminate\Database\Seeder;

class PhoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phones = [
            [
                'model' => 'iPhone 14 Pro Max',
                'price' => 1099.99,
                'stock' => 10,
            ],
            [
                'model' => 'Samsung Galaxy S23 Ultra',
                'price' => 1199.99,
                'stock' => 15,
            ],
            [
                'model' => 'Google Pixel 7 Pro',
                'price' => 899.99,
                'stock' => 8,
            ],
            [
                'model' => 'OnePlus 11',
                'price' => 699.99,
                'stock' => 12,
            ],
            [
                'model' => 'Xiaomi 13 Pro',
                'price' => 899.99,
                'stock' => 20,
            ],
        ];

        foreach ($phones as $phone) {
            Phone::create($phone);
        }
    }
} 