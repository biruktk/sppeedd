<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $brands = ['Toyota Genuine', 'Bosch', 'Denso', 'NGK', 'Castrol', 'Michelin'];

        for ($i = 0; $i < 50; $i++) {
            $purchasePrice = fake()->randomFloat(2, 5, 400);
            $sellingPrice = $purchasePrice * 1.25;
            
            Item::create([
                'item_name' => fake()->words(3, true),
                'part_number' => strtoupper(fake()->bothify('??-####-??')),
                'brand' => fake()->randomElement($brands),
                'quantity' => fake()->numberBetween(0, 100),
                'unit' => 'pcs',
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'unit_price' => $sellingPrice,
                'total_price' => $sellingPrice * 10, // Just a dummy total
                'minimum_quantity' => 5,
                'low_quantity' => 10,
                'location' => 'Shelf ' . fake()->bothify('#?'),
                'condition' => 'New',
            ]);
        }
    }
}
