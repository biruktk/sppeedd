<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreItem;

class StoreItemSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['Toyota Genuine', 'Bosch', 'Denso', 'NGK', 'Castrol', 'Michelin'];
        $models = ['Corolla', 'Hilux', 'Land Cruiser', 'Camry', 'RAV4'];

        for ($i = 0; $i < 30; $i++) {
            $unitPrice = fake()->randomFloat(2, 10, 500);
            $quantity = fake()->numberBetween(1, 50);
            
            StoreItem::create([
                'code' => strtoupper(fake()->bothify('ITEM-####')),
                'description' => fake()->words(3, true),
                'partNumber' => strtoupper(fake()->bothify('PN-####-??')),
                'quantity' => $quantity,
                'brand' => fake()->randomElement($brands),
                'model' => fake()->randomElement($models),
                'condition' => 'New',
                'unitPrice' => $unitPrice,
                'totalPrice' => $unitPrice * $quantity,
                'location' => 'Shelf ' . fake()->bothify('#?'),
            ]);
        }
    }
}
