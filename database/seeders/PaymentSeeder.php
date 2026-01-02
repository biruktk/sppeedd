<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Customer;

class PaymentSeeder extends Seeder
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
            $paidAmount = fake()->randomFloat(2, 500, 5000);
            
            Payment::create([
                'date' => fake()->date(),
                'name' => $customer->name,
                'reference' => 'PAY-' . strtoupper(fake()->bothify('####-??')),
                'fs' => fake()->bothify('FS-####'),
                'mobile' => $customer->telephone,
                'tin' => fake()->numerify('#########'),
                'vat' => 15,
                'method' => fake()->randomElement(['cash', 'transfer', 'card', 'cheque']),
                'status' => fake()->randomElement(['Paid', 'Pending']),
                'paidAmount' => $paidAmount,
                'remainingAmount' => fake()->randomFloat(2, 0, 1000),
                'paidBy' => $customer->name,
                'approvedBy' => 'Admin',
                'reason' => 'Service Payment',
                'remarks' => fake()->sentence(),
                'labourCosts' => [],
                'spareCosts' => [],
                'otherCosts' => [],
                'summary' => ['total' => $paidAmount],
            ]);
        }
    }
}
