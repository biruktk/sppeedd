<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Item;
use App\Models\Customer;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $items = Item::all();

        if ($customers->isEmpty()) $this->call(CustomerSeeder::class);
        if ($items->isEmpty()) $this->call(ItemSeeder::class);

        $customers = Customer::all();
        $items = Item::all();

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $subTotal = 0;
            
            $sale = Sale::create([
                'ref_num' => 'SAL-' . strtoupper(fake()->bothify('####-??')),
                'sales_date' => fake()->date(),
                'customer_name' => $customer->name,
                'mobile' => $customer->telephone,
                'email' => fake()->unique()->safeEmail,
                'address' => fake()->address,
                'vat_rate' => 15,
                'discount' => fake()->randomFloat(2, 0, 50),
                'payment_status' => fake()->randomElement(['Paid', 'Partial', 'Unpaid']),
                'payment_type' => fake()->randomElement(['Cash', 'Bank Transfer', 'Credit Card']),
                'status' => fake()->randomElement(['Pending', 'Completed', 'Canceled']),
            ]);

            for ($j = 0; $j < fake()->numberBetween(1, 5); $j++) {
                $item = $items->random();
                $qty = fake()->numberBetween(1, 5);
                $price = $item->unit_price * 1.2; // 20% markup
                
                $sale->items()->attach($item->id, [
                    'item_name' => $item->item_name,
                    'part_number' => $item->part_number,
                    'brand' => $item->brand,
                    'unit' => $item->unit,
                    'selling_price' => $price,
                    'sale_quantity' => $qty,
                ]);
                
                $subTotal += ($price * $qty);
            }

            $vatAmount = $subTotal * 0.15;
            $totalAmount = $subTotal + $vatAmount - $sale->discount;
            
            $sale->update([
                'sub_total' => $subTotal,
                'total_amount' => $totalAmount,
                'paid_amount' => $sale->payment_status === 'Paid' ? $totalAmount : ($sale->payment_status === 'Partial' ? $totalAmount / 2 : 0),
                'due_amount' => $sale->payment_status === 'Paid' ? 0 : ($sale->payment_status === 'Partial' ? $totalAmount / 2 : $totalAmount),
            ]);
        }
    }
}
