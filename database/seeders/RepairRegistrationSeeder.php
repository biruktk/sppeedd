<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RepairRegistration;
use App\Models\Vehicle;
use App\Models\Customer;

class RepairRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->call(CustomerSeeder::class);
            $customers = Customer::all();
        }

        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            
            $repair = RepairRegistration::create([
                'customer_name' => $customer->name,
                'customer_type' => fake()->randomElement(['Individual', 'Corporate']),
                'mobile' => $customer->telephone,
                'received_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'estimated_date' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
                'promise_date' => fake()->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
                'priority' => fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']),
                'repair_category' => [fake()->randomElement(['Mechanical', 'Electrical', 'Body Work', 'Maintenance'])],
                'customer_observation' => [fake()->sentence()],
                'spare_change' => [fake()->word()],
                'job_description' => [fake()->sentence()],
                'received_by' => fake()->name,
                'selected_items' => [],
            ]);

            Vehicle::create([
                'repair_registration_id' => $repair->id,
                'plate_no' => strtoupper(fake()->bothify('???-####')),
                'model' => fake()->randomElement(['Toyota Corolla', 'Honda Civic', 'Ford F-150', 'BMW 3 Series', 'Mercedes C-Class']),
                'vin' => strtoupper(fake()->bothify('*****************')),
                'condition' => fake()->randomElement(['Good', 'Fair', 'Poor']),
                'tin' => fake()->numerify('#########'),
                'year' => fake()->year(),
                'km_reading' => fake()->numberBetween(1000, 200000),
                'estimated_price' => fake()->randomFloat(2, 100, 5000),
            ]);
        }
    }
}
