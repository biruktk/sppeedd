<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $positions = ['Mechanic', 'Electrician', 'Painter', 'Supervisor', 'Manager', 'Receptionist'];

        for ($i = 0; $i < 10; $i++) {
            Employee::create([
                'full_name' => fake()->name,
                'contact_information' => fake()->phoneNumber,
                'position' => fake()->randomElement($positions),
                'address' => fake()->address,
                'gender' => fake()->randomElement(['Male', 'Female']),
            ]);
        }
    }
}
