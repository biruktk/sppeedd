<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function index()
    {
        $setting = CompanySetting::first();
        return response()->json($setting);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_am' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'tin' => 'nullable|string',
            'vat' => 'nullable|string',
            'website' => 'nullable|string',
            'business_type' => 'nullable|string',
            'tagline' => 'nullable|string',

            // ✅ Changed from digits:4 to plain string
            'established' => 'nullable|string|max:50',

            // ✅ NEW field
            'login_page_name' => 'nullable|string|max:255',
            'login_page_name_am' => 'nullable|string|max:255',


            'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048',

            'date_format' => 'nullable|string',
            'payment_ref_start' => 'nullable|string|max:20',
            'proforma_ref_start' => 'nullable|string|max:20',
        ]);

        $setting = CompanySetting::firstOrNew([]);

        // ✅ Handle logo upload
        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            $file = $request->file('logo');
            $filename = $file->store('logos', 'public');
            $validated['logo'] = $filename;
        }

        $setting->fill($validated);
        $setting->save();

        return response()->json($setting);
    }
}

