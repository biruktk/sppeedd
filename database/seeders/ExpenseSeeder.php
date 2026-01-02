<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\RepairRegistration;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $repairs = RepairRegistration::all();

        $categories = ['Utilities', 'Rent', 'Salaries', 'Spare Parts', 'Marketing', 'Maintenance', 'Other'];

        for ($i = 0; $i < 20; $i++) {
            $repair = $repairs->isEmpty() ? null : $repairs->random();
            
            Expense::create([
                'date' => fake()->date(),
                'category' => fake()->randomElement($categories),
                'amount' => fake()->randomFloat(2, 10, 2000),
                'payment_method' => fake()->randomElement(['Cash', 'Bank Transfer', 'Cheque']),
                'reference_no' => 'EXP-' . strtoupper(fake()->bothify('####-??')),
                'paid_by' => fake()->name,
                'approved_by' => 'Admin',
                'remarks' => fake()->sentence(),
                'job_id' => $repair ? $repair->id : null,
                'vendor_name' => fake()->company,
            ]);
        }
    }
}
