<?php

namespace App\Http\Controllers;

use App\Models\PostDriveTest;
use App\Models\RepairRegistration;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class RepairRegistrationController extends Controller
{
    public function index()
{
    try {
        $repairs = RepairRegistration::with('vehicles')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'count' => $repairs->count(),
            'data' => $repairs
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error fetching repairs',
            'error' => $e->getMessage()
        ], 500);
    }
}

    
    
   



public function store(Request $request)
{
    if (!$request->isMethod('post')) {
        return response()->json(['error' => 'Invalid request method'], 405);
    }

    $payload = json_decode($request->input('payload'), true);

    if (!$payload) {
        return response()->json(['message' => 'Invalid JSON payload'], 400);
    }

    $validated = validator($payload, [
        'customer_name' => 'required|string|max:255',
        'customer_type' => 'required|string|max:255',
        'mobile' => 'required|string|max:20',
        'received_date' => 'required|date',
        'estimated_date' => 'nullable|string',
        'promise_date' => 'nullable|date',
        'priority' => 'required|string',
        'repair_category' => 'required|array',
        'customer_observation' => 'nullable|array',
        'spare_change' => 'nullable|array',
        'job_description' => 'nullable|array',
        'received_by' => 'nullable|string',
        'vehicles' => 'required|array',
        'vehicles.*.plate_no' => 'required|string',
        'vehicles.*.model' => 'required|string',
        'vehicles.*.vin' => 'nullable|string',
        'vehicles.*.condition' => 'nullable|string',
        'vehicles.*.tin' => 'nullable|string',
        'vehicles.*.year' => 'nullable|string',
        'vehicles.*.km_reading' => 'nullable|integer',
        'vehicles.*.estimated_price' => 'nullable|numeric',
        'selected_items' => 'nullable|array',
    ])->validate();

    try {
        // Generate job_id
        $lastJob = RepairRegistration::latest('job_id')->first();
        $nextJobId = $lastJob ? str_pad(((int)$lastJob->job_id) + 1, 4, '0', STR_PAD_LEFT) : '0001';

        while (RepairRegistration::where('job_id', $nextJobId)->exists()) {
            $nextJobId = str_pad(((int)$nextJobId) + 1, 4, '0', STR_PAD_LEFT);
        }

        // Store repair
        $repair = new RepairRegistration();
        $repair->job_id = $nextJobId;
        $repair->customer_name = $payload['customer_name'];
        $repair->customer_type = $payload['customer_type'];
        $repair->mobile = $payload['mobile'];
        $repair->received_date = $payload['received_date'];
        $repair->estimated_date = $payload['estimated_date'];
        $repair->promise_date = $payload['promise_date'];
        $repair->priority = $payload['priority'];
        $repair->repair_category = $payload['repair_category'];
        $repair->customer_observation = $payload['customer_observation'];
        $repair->spare_change = $payload['spare_change'];
        $repair->job_description = $payload['job_description'];
        $repair->received_by = $payload['received_by'];
        $repair->selected_items = $payload['selected_items'];

        // Handle image uploads (before saving to get path in DB)
        $imageFields = ['front', 'back', 'left', 'right', 'top'];
        foreach ($imageFields as $dir) {
            $field = "car_image_$dir";
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . "_$dir." . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/car_images', $filename);
                $repair->$field = $path;
            }
        }

        $repair->save();

        // Save vehicles
        foreach ($payload['vehicles'] as $vehicle) {
            $repair->vehicles()->create($vehicle);
        }

        return response()->json(['message' => 'Repair created successfully', 'job_id' => $repair->job_id], 201);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Error storing repair', 'error' => $e->getMessage()], 500);
    }
}













    

public function update(Request $request, $id)
{
    if (!$request->isMethod('put')) {
        return response()->json(['error' => 'Invalid request method'], 405);
    }

    $repair = RepairRegistration::find($id);

    if (!$repair) {
        return response()->json(['message' => 'Repair not found'], 404);
    }

    $validated = $request->validate([
        'job_id' => 'required|string|unique:repair_registrations,job_id,' . $repair->id,
        'customer_name' => 'required|string|max:255',
        'customer_type' => 'required|string|max:255',
        'mobile' => 'required|string|max:20',
        'received_date' => 'required|date',
        'estimated_date' => 'nullable|string',
        'promise_date' => 'nullable|date',
        'priority' => 'required|string',
        'repair_category' => 'required|array',
        'customer_observation' => 'nullable|array',
        'spare_change' => 'nullable|array',
        'job_description' => 'nullable|array',
        'received_by' => 'nullable|string',
        'vehicles' => 'required|array|min:1',
        'vehicles.*.plate_no' => 'required|string',
        'vehicles.*.model' => 'required|string',
        'vehicles.*.vin' => 'nullable|string',
        'vehicles.*.condition' => 'nullable|string',
        'vehicles.*.tin' => 'nullable|string',
        'vehicles.*.year' => 'nullable|string',
        'vehicles.*.km_reading' => 'nullable|integer',
        'vehicles.*.estimated_price' => 'nullable|numeric',
        'selected_items' => 'nullable|array',
    ]);

    try {
        // Update Repair Registration details
        $repair->update([
            'job_id' => $validated['job_id'],
            'customer_name' => $validated['customer_name'],
            'customer_type' => $validated['customer_type'],
            'mobile' => $validated['mobile'],
            'received_date' => $validated['received_date'],
            'estimated_date' => $validated['estimated_date'],
            'promise_date' => $validated['promise_date'],
            'priority' => $validated['priority'],
            'repair_category' => json_encode($validated['repair_category'], JSON_THROW_ON_ERROR),
            'customer_observation' => json_encode($validated['customer_observation'] ?? [], JSON_THROW_ON_ERROR),
            'spare_change' => json_encode($validated['spare_change'] ?? [], JSON_THROW_ON_ERROR),
            'job_description' => json_encode($validated['job_description'] ?? [], JSON_THROW_ON_ERROR),
            'received_by' => $validated['received_by'],
            'selected_items' => json_encode($validated['selected_items'] ?? [], JSON_THROW_ON_ERROR),
        ]);

        // ✅ Delete old vehicles before adding new ones (avoids duplicates)
        $repair->vehicles()->delete();

        // ✅ Insert new vehicle data
        foreach ($validated['vehicles'] as $vehicleData) {
            $repair->vehicles()->create([
                'plate_no' => $vehicleData['plate_no'],
                'model' => $vehicleData['model'],
                'vin' => $vehicleData['vin'] ?? null,
                'condition' => $vehicleData['condition'],
                'tin' => $vehicleData['tin'] ?? null,
                'year' => $vehicleData['year'] ?? null,
                'km_reading' => $vehicleData['km_reading'] ?? null,
                'estimated_price' => $vehicleData['estimated_price'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Repair updated successfully'], 200);
    } catch (\JsonException $e) {
        return response()->json(['message' => 'JSON encoding error', 'error' => $e->getMessage()], 500);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating repair', 'error' => $e->getMessage()], 500);
    }
}




public function import(Request $request)
{
    $rawRepairs = $request->input('repairs', []);

    if (empty($rawRepairs)) {
        return response()->json(['message' => 'No data found in import'], 400);
    }

    // Normalize and clean up
    $normalized = collect($rawRepairs)->map(function ($r) {
        return collect($r)->map(function ($v) {
            return $v === '' ? null : (is_numeric($v) ? (string)$v : $v);
        })->toArray();
    })->toArray();

    // Validate
    $validated = validator(
        ['repairs' => $normalized],
        [
            'repairs' => 'required|array|min:1',
            'repairs.*.customer name' => 'required|string|max:255',
            'repairs.*.customer type' => 'nullable|string|max:255',
            'repairs.*.mobile' => 'nullable|string|max:50',
            'repairs.*.priority' => 'nullable|string|max:50',
            'repairs.*.repair category' => 'nullable|string|max:255',
            'repairs.*.recived date' => 'nullable|date',
            'repairs.*.plate number' => 'required|string|max:50',
            'repairs.*.model' => 'nullable|string|max:255',
            'repairs.*.condition' => 'nullable|string|max:255',
            'repairs.*.year' => 'nullable|string|max:10',
            'repairs.*.km_reading' => 'nullable|string|max:20',
            'repairs.*.tin' => 'nullable|string|max:255',
            'repairs.*.vin' => 'nullable|string|max:255',
        ]
    )->validate();

    $repairs = $validated['repairs'];

    foreach ($repairs as $data) {
        $vehicle = [
            'plate_no' => $data['plate number'] ?? null,
            'model' => $data['model'] ?? null,
            'condition' => $data['condition'] ?? null,
            'year' => $data['year'] ?? null,
            'km_reading' => $data['km_reading'] ?? null,
            'tin' => $data['tin'] ?? null,
            'vin' => $data['vin'] ?? null,
        ];

        $repairData = [
            'customer_name' => $data['customer name'] ?? '',
            'customer_type' => $data['customer type'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'priority' => $data['priority'] ?? '',
            'repair_category' => $data['repair category'] ?? '',
            'received_date' => $data['recived date'] ?? now(),
        ];

        $repair = \App\Models\RepairRegistration::updateOrCreate(
            ['customer_name' => $repairData['customer_name'], 'mobile' => $repairData['mobile']],
            $repairData
        );

        $repair->vehicles()->updateOrCreate(['plate_no' => $vehicle['plate_no']], $vehicle);
    }

    return response()->json(['message' => 'Imported successfully']);
}


public function destroy($id)
{
    $repair = RepairRegistration::find($id);

    if (!$repair) {
        return response()->json(['message' => 'Repair not found'], 404);
    }

    $repair->delete();
    return response()->json(['message' => 'Repair deleted successfully'], 200);
}

public function show($id)
{
    $repair = RepairRegistration::with('vehicles')->find($id);

    if (!$repair) {
        return response()->json(['message' => 'Repair not found.'], 404);
    }

    // Add image URLs (if stored)
    $imageFields = ['front', 'back', 'left', 'right', 'top'];
    $imageUrls = [];

    foreach ($imageFields as $dir) {
        $field = "car_image_$dir";
        $imageUrls[$field] = $repair->$field
            ? asset('storage/' . str_replace('public/', '', $repair->$field))
            : null;
    }

    return response()->json([
        'id' => $repair->id,
        'job_id' => $repair->job_id,
        'customer_name' => $repair->customer_name,
        'customer_type' => $repair->customer_type,
        'mobile' => $repair->mobile,
        'promise_date' => $repair->promise_date,
        'estimated_date' => $repair->estimated_date,
        'priority' => $repair->priority,
        'received_date' => $repair->received_date,
        'received_by' => $repair->received_by,
        'status'=>$repair->status,
        'plate_no' => optional($repair->vehicles->first())->plate_no,
        'repair_category' => $repair->repair_category,
        'customer_observation' => $repair->customer_observation,
        'job_description' => $repair->job_description,
        'selected_items' => $repair->selected_items,
        'spare_change' => $repair->spare_change,
        'labor' => property_exists($repair, 'labor') ? $repair->labor : null,
        'labor_price' => property_exists($repair, 'labor_price') ? $repair->labor_price : null,
        'spare_used' => property_exists($repair, 'spare_used') ? $repair->spare_used : null,
        'vehicles' => $repair->vehicles->map(function ($vehicle) {
            return [
                'model' => $vehicle->model,
                'vin' => $vehicle->vin,
                'tin' => $vehicle->tin,
                'plate_no' => $vehicle->plate_no,
                'km_reading' => $vehicle->km_reading,
                'estimated_price' => $vehicle->estimated_price,
                'year' => $vehicle->year,
                'condition' => $vehicle->condition,
            ];
        }),
        // ✅ Add images at the bottom
        'car_images' => $imageUrls,
    ]);
}


public function showBasicInfo($id)
{
    try {
        $repair = RepairRegistration::find($id);

        if (!$repair) {
            return response()->json(['message' => 'Repair not found.'], 404);
        }

        $plate = Vehicle::where('repair_registration_id', $id)
                        ->select('plate_no')
                        ->first();

        return response()->json([
            'customer_name'     => $repair->customer_name,
            'plate_no'          => optional($plate)->plate_no,
            'repair_category'   => $repair->repair_category, // <-- This line added
        ]);
    } catch (\Exception $e) {
        // \Log::error('showBasicInfo error: ' . $e->getMessage());
        return response()->json(['message' => 'Server error'], 500);
    }
}









public function getVehiclesByRepairId($id)
{
    $repair = RepairRegistration::find($id);

    if (!$repair) {
        return response()->json(['message' => 'Repair not found'], 404);
    }

    return response()->json(['vehicles' => $repair->vehicles]);
}


public function totalRepairs()
{
    try {
        $count = RepairRegistration::count();
        return response()->json(['total_repairs' => $count]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error fetching total repairs', 'message' => $e->getMessage()], 500);
    }
}



public function deleteRepairs(Request $request)
{
    // Debugging: Check if repair_ids is passed correctly
    // dd($request->repair_ids); // Uncomment for debugging purposes

    // Validation for the repair_ids array
    $request->validate([
        'repair_ids' => 'required|array', // repair_ids must be an array
        'repair_ids.*' => 'exists:repair_registrations,id' // Each id must exist in the repair_registrations table
    ]);

    // Delete the repairs
    $deleted = RepairRegistration::whereIn('id', $request->repair_ids)->delete();

    // Check if any records were deleted
    if ($deleted > 0) {
        return response()->json(['message' => 'Selected repairs deleted successfully']);
    } else {
        return response()->json(['message' => 'No repairs found to delete'], 404);
    }
}


public function updateStatus(Request $request, $id)
{
    $repair = RepairRegistration::find($id);

    if (!$repair) {
        return response()->json(['error' => 'Repair registration not found'], 404);
    }

    $repair->status = $request->status; // Get the new status from request
    $repair->save();

    return response()->json(['message' => 'Status updated successfully!', 'status' => $repair->status]);
}

public function updatestat(Request $request, $id)
{
    $repair = RepairRegistration::find($id);
    if (!$repair) {
        return response()->json(['message' => 'Repair not found.'], 404);
    }

    $repair->status = $request->input('status');
    $repair->save();

    return response()->json(['message' => 'Status updated successfully.']);
}
public function getByJobId($jobId)
{
    $repair = RepairRegistration::with('vehicles')
        ->where('job_id', $jobId)
        ->first();

    if (!$repair) {
        return response()->json(['message' => 'Repair not found.'], 404);
    }

    return response()->json([
        'job_id' => $repair->job_id,
        'plate_no' => optional($repair->vehicles->first())->plate_no,
        'customer_name' => $repair->customer_name,
        'mobile' => $repair->mobile,

    ]);
}



public function getBatchByJobIds(Request $request)
{
    $jobIds = $request->input('job_ids', []);

    $repairs = RepairRegistration::with('vehicles')
        ->whereIn('job_id', $jobIds)
        ->get();

    // Make sure testDrives keys are padded
    $testDrives = PostDriveTest::whereIn('job_card_no', $jobIds)
        ->get()
        ->keyBy(function ($item) {
            return str_pad($item->job_card_no, 4, '0', STR_PAD_LEFT);
        });

    $result = [];

    foreach ($repairs as $repair) {
        $jobId = str_pad($repair->job_id, 4, '0', STR_PAD_LEFT);
        $vehicle = $repair->vehicles->first();

        $testDrive = $testDrives[$jobId] ?? null;

        $result[$jobId] = [
            'plate_no' => $vehicle?->plate_no,
            'start_date' => $repair->received_date,
            'end_date' => $repair->promise_date,
            'status' => $repair->status,
            'received_date' => $testDrive?->checked_date ?? null,
            'technician_final_approval' => $testDrive?->technician_final_approval ?? null,
        ];
    }

    return response()->json($result);
}







}
