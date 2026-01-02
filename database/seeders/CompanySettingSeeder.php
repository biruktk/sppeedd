<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanySetting;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::create([
            'name_en' => 'NILE GARAGE',
            'name_am' => 'ናይል ጋራዥ',
            'phone' => '+251 911 123 456',
            'email' => 'info@nilegarage.com',
            'address' => 'Addis Ababa, Ethiopia',
            'tin' => '123456789',
            'vat' => '987654321',
            'business_type' => 'Automotive Repair',
            'tagline' => 'Quality Service for Your Vehicle',
            'established' => 'September 2021',
            'login_page_name' => 'NILE GARAGE',
            'login_page_name_am' => 'ናይል ጋራዥ',
            'date_format' => 'DD/MM/YYYY',
            'payment_ref_start' => 'PAY-0001',
            'proforma_ref_start' => 'PROF-0001',
        ]);
    }
}
