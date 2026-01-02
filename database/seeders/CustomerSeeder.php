<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 20; $i++) {
            Customer::create([
                'name' => fake()->name,
                'customerType' => fake()->randomElement(['Individual', 'Corporate']),
                'telephone' => fake()->phoneNumber,
                'carModels' => fake()->randomElement(['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes']),
            ]);
        }
    }
}
