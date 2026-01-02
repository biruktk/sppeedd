<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CompanySettingSeeder::class,
            CustomerSeeder::class,
            EmployeeSeeder::class,
            ItemSeeder::class,
            StoreItemSeeder::class,
            RepairRegistrationSeeder::class,
            WorkOrderSeeder::class,
            SaleSeeder::class,
            PurchaseSeeder::class,
            PaymentSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}
