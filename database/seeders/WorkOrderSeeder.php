<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkOrder;
use App\Models\WorkDetail;
use App\Models\RepairRegistration;
use App\Models\Vehicle;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $repairs = RepairRegistration::all();

        if ($repairs->isEmpty()) {
            $this->call(RepairRegistrationSeeder::class);
            $repairs = RepairRegistration::all();
        }

        foreach ($repairs as $repair) {
            $vehicle = Vehicle::where('repair_registration_id', $repair->id)->first();
            
            $workOrder = WorkOrder::create([
                'job_card_no' => $repair->job_id,
                'plate_number' => $vehicle ? $vehicle->plate_no : strtoupper(fake()->bothify('???-####')),
                'customer_name' => $repair->customer_name,
                'repair_category' => $repair->repair_category,
                'work_details' => [],
            ]);

            for ($j = 0; $j < fake()->numberBetween(1, 4); $j++) {
                $cost = fake()->randomFloat(2, 50, 500);
                $laborTime = fake()->numberBetween(1, 8);
                
                WorkDetail::create([
                    'work_order_id' => $workOrder->id,
                    'workDescription' => fake()->sentence(),
                    'laborTime' => $laborTime,
                    'cost' => $cost,
                    'total' => $cost * $laborTime,
                    'startDate' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
                    'endDate' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
                    'status' => fake()->randomElement(['Pending', 'In Progress', 'Completed']),
                ]);
            }
        }
    }
}
