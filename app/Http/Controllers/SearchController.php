<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\WorkOrder;
use App\Models\RepairRegistration;
use App\Models\Item;
use App\Models\StoreItem;
use App\Models\SpareRequest;
use App\Models\Inspection;
use App\Models\WheelAlignemnt;
use App\Models\Bolo;
use App\Models\Admin;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function universalSearch(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $limit = $request->input('limit', 20);

        if (!$query) {
            return response()->json(['error' => 'Search query is required'], 400);
        }

        $results = [];
        $searchableModels = $this->getSearchableModels();

        foreach ($searchableModels as $modelClass => $config) {
            if ($type !== 'all' && $type !== $config['type']) {
                continue;
            }

            $model = new $modelClass;
            $queryBuilder = $model->newQuery();

            $queryBuilder->where(function ($q) use ($query, $config) {
                foreach ($config['fields'] as $field) {
                    $q->orWhere($field, 'LIKE', "%{$query}%");
                }
            });
            
            // Add relationships if needed
            if (isset($config['with'])) {
                $queryBuilder->with($config['with']);
            }

            $results[$config['type']] = $queryBuilder->limit($limit)->get($config['select']);
        }

        return response()->json($results);
    }

    public function advancedSearch(Request $request)
    {
        $query = $request->input('q');
        $filters = $request->input('filters', []);
        $sort = $request->input('sort', 'relevance');
        $limit = $request->input('limit', 50);

        if (!$query) {
            return response()->json(['error' => 'Search query is required'], 400);
        }

        $results = [];
        $searchTerm = "%{$query}%";

        foreach ($this->getSearchableModels() as $modelClass => $config) {
            $model = new $modelClass;
            $queryBuilder = $model->newQuery();

            // Apply text search across searchable fields
            $queryBuilder->where(function ($q) use ($searchTerm, $config) {
                foreach ($config['fields'] as $field) {
                    $q->orWhere($field, 'LIKE', $searchTerm);
                }
            });

            // Apply filters
            if (isset($filters[$config['type']])) {
                foreach ($filters[$config['type']] as $field => $value) {
                    if ($value) {
                        $queryBuilder->where($field, $value);
                    }
                }
            }

            // Apply sorting
            if ($sort === 'relevance') {
                $queryBuilder->orderByRaw("CASE WHEN " . implode(" LIKE ? OR ", $config['fields']) . " LIKE ? THEN 1 ELSE 2 END", 
                    array_fill(0, count($config['fields']) + 1, $searchTerm));
            } else {
                $queryBuilder->orderBy($sort, 'desc');
            }
            
             // Add relationships if needed
            if (isset($config['with'])) {
                $queryBuilder->with($config['with']);
            }

            $results[$config['type']] = $queryBuilder->limit($limit)->get($config['select']);
        }

        return response()->json($results);
    }

    private function getSearchableModels()
    {
        return [
            Customer::class => [
                'type' => 'customers',
                'fields' => ['name', 'email', 'phone', 'address'],
                'select' => ['id', 'name', 'email', 'phone', 'address']
            ],
            Employee::class => [
                'type' => 'employees',
                'fields' => ['name', 'email', 'phone', 'position', 'department'],
                'select' => ['id', 'name', 'email', 'phone', 'position', 'department']
            ],
            WorkOrder::class => [
                'type' => 'work_orders',
                'fields' => ['job_card_no', 'description', 'status'],
                'select' => ['id', 'job_card_no', 'description', 'status', 'customer_id'],
                'with' => ['customer:id,name']
            ],
            RepairRegistration::class => [
                'type' => 'repairs',
                'fields' => ['job_card_no', 'vehicle_make', 'vehicle_model', 'plate_number', 'chassis_number'],
                'select' => ['id', 'job_card_no', 'vehicle_make', 'vehicle_model', 'plate_number', 'chassis_number', 'customer_id'],
                'with' => ['customer:id,name']
            ],
            Item::class => [
                'type' => 'items',
                'fields' => ['name', 'part_number', 'description', 'category', 'brand'],
                'select' => ['id', 'name', 'part_number', 'description', 'category', 'brand', 'quantity', 'price']
            ],
            StoreItem::class => [
                'type' => 'store_items',
                'fields' => ['name', 'part_number', 'description', 'category', 'location'],
                'select' => ['id', 'name', 'part_number', 'description', 'category', 'location', 'quantity', 'price']
            ],
            SpareRequest::class => [
                'type' => 'spare_requests',
                'fields' => ['request_code', 'status', 'priority'],
                'select' => ['id', 'request_code', 'status', 'priority', 'work_order_id'],
                'with' => ['workOrder:id,job_card_no,description']
            ],
            Inspection::class => [
                'type' => 'inspections',
                'fields' => ['job_card_no', 'inspection_type', 'status', 'findings', 'recommendations'],
                'select' => ['id', 'job_card_no', 'inspection_type', 'status', 'findings', 'recommendations']
            ],
            WheelAlignemnt::class => [
                'type' => 'wheel_alignments',
                'fields' => ['job_card_no', 'alignment_type', 'status', 'findings', 'recommendations'],
                'select' => ['id', 'job_card_no', 'alignment_type', 'status', 'findings', 'recommendations']
            ],
            Bolo::class => [
                'type' => 'bolos',
                'fields' => ['job_card_no', 'bolo_type', 'status', 'findings', 'recommendations'],
                'select' => ['id', 'job_card_no', 'bolo_type', 'status', 'findings', 'recommendations']
            ],
            Admin::class => [
                'type' => 'users',
                'fields' => ['name', 'username', 'email'],
                'select' => ['id', 'name', 'username', 'email'],
                'with' => ['roles:name']
            ],
             \Spatie\Permission\Models\Role::class => [
                'type' => 'roles',
                'fields' => ['name'],
                'select' => ['id', 'name']
            ],
             \Spatie\Permission\Models\Permission::class => [
                'type' => 'permissions',
                'fields' => ['name'],
                'select' => ['id', 'name']
            ]
        ];
    }
}
