<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /** ----------------- CREATE ----------------- */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_date' => 'required|date',
            'supplier_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.sale_quantity' => 'required|integer',
            'items.*.part_number' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $purchaseOrder = PurchaseOrder::create([
                'sales_date' => $request->sales_date,
                'supplier_name' => $request->supplier_name,
                'company_name' => $request->company_name,
                'reference_number' => $request->reference_number,
                'tin_number' => $request->tin_number,
                'mobile' => $request->mobile,
                'office' => $request->office,
                'phone' => $request->phone,
                'website' => $request->website,
                'email' => $request->email,
                'address' => $request->address,
                'bank_account' => $request->bank_account,
                'other_info' => $request->other_info,
                'remark' => $request->remark,
                'status' => $request->status ?? 'Pending',
            ]);

            foreach ($request->items as $item) {
                $purchaseOrder->items()->create([
                    'item_name' => $item['item_name'] ?? '',
                    'part_number' => $item['part_number'],
                    'brand' => $item['brand'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'unit_price' => $item['unit_price'],
                    'sale_quantity' => $item['sale_quantity'],
                ]);

                $existingItem = Item::where('part_number', $item['part_number'])->first();
                if ($existingItem) {
                    $existingItem->quantity += $item['sale_quantity'];
                    $existingItem->save();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully.',
                'data' => $purchaseOrder->load('items'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to store purchase order.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /** ----------------- READ ALL ----------------- */
    public function index()
    {
        $orders = PurchaseOrder::with('items')->latest()->get();
        return response()->json($orders);
    }

    /** ----------------- READ ONE (View) ----------------- */
    public function show($reference_number)
    {
        $order = PurchaseOrder::with('items')
            ->where('reference_number', $reference_number)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Purchase order not found.'], 404);
        }

        return response()->json($order);
    }

    /** ----------------- UPDATE (Edit) ----------------- */
    public function update(Request $request, $reference_number)
    {
        $order = PurchaseOrder::where('reference_number', $reference_number)->first();

        if (!$order) {
            return response()->json(['error' => 'Purchase order not found.'], 404);
        }

        DB::beginTransaction();

        try {
            $order->update($request->only([
                'sales_date',
                'supplier_name',
                'company_name',
                'tin_number',
                'mobile',
                'office',
                'phone',
                'website',
                'email',
                'address',
                'bank_account',
                'other_info',
                'remark',
                'status',
            ]));

            // Optional: Update items
            if ($request->has('items')) {
                $order->items()->delete(); // delete old items
                foreach ($request->items as $item) {
                    $order->items()->create([
                        'item_name' => $item['item_name'] ?? '',
                        'part_number' => $item['part_number'],
                        'brand' => $item['brand'] ?? '',
                        'unit' => $item['unit'] ?? '',
                        'unit_price' => $item['unit_price'],
                        'sale_quantity' => $item['sale_quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order updated successfully.',
                'data' => $order->load('items'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update purchase order.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /** ----------------- DELETE ----------------- */
    public function destroy($reference_number)
    {
        $order = PurchaseOrder::where('reference_number', $reference_number)->first();

        if (!$order) {
            return response()->json(['error' => 'Purchase order not found.'], 404);
        }

        DB::beginTransaction();

        try {
            $order->items()->delete();
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Purchase order deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to delete purchase order.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /** ----------------- PRINT ----------------- */
    public function print($reference_number)
    {
        $order = PurchaseOrder::with('items')
            ->where('reference_number', $reference_number)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Purchase order not found.'], 404);
        }

        // You can later convert this to a PDF or printable view
        return response()->json([
            'message' => 'Printable purchase order data.',
            'data' => $order,
        ]);
    }
}
