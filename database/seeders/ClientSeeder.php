<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@example.com',
                'credit_score' => 750.5,
                'cc' => '1234567890',
                'phone_number' => '3001234567',
            ],
            [
                'name' => 'María González',
                'email' => 'maria.gonzalez@example.com',
                'credit_score' => 820.0,
                'cc' => '0987654321',
                'phone_number' => '3109876543',
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@example.com',
                'credit_score' => 680.5,
                'cc' => '5678901234',
                'phone_number' => '3507654321',
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@example.com',
                'credit_score' => 790.0,
                'cc' => '4321098765',
                'phone_number' => '3201234567',
            ],
            [
                'name' => 'Luis Sánchez',
                'email' => 'luis.sanchez@example.com',
                'credit_score' => 705.5,
                'cc' => '9876543210',
                'phone_number' => '3157654321',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
} 