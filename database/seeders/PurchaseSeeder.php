<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Item;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $items = Item::all();

        if ($items->isEmpty()) {
            $this->call(ItemSeeder::class);
            $items = Item::all();
        }

        for ($i = 0; $i < 10; $i++) {
            $purchase = PurchaseOrder::create([
                'reference_number' => 'PUR-' . strtoupper(fake()->bothify('####-??')),
                'sales_date' => fake()->date(),
                'supplier_name' => fake()->company,
                'company_name' => fake()->company,
                'tin_number' => fake()->numerify('#########'),
                'mobile' => fake()->phoneNumber,
                'email' => fake()->companyEmail,
                'address' => fake()->address,
                'bank_account' => fake()->bankAccountNumber,
                'remark' => fake()->sentence(),
            ]);

            for ($j = 0; $j < fake()->numberBetween(1, 5); $j++) {
                $item = $items->random();
                $qty = fake()->numberBetween(10, 50);
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchase->id,
                    'item_name' => $item->name,
                    'part_number' => $item->part_number,
                    'brand' => $item->brand,
                    'unit' => 'pcs',
                    'unit_price' => $item->unit_price,
                    'sale_quantity' => $qty,
                ]);
            }
        }
    }
}
