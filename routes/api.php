<?php

use App\Http\Controllers\JobCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\BolloController;
use App\Http\Controllers\ConditionOfVehicleController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\RepairRegistrationController;
use App\Http\Controllers\WheelAlignemntController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DailyProgressController;
use App\Http\Controllers\DurinDriveTestController;
use App\Http\Controllers\SpareRequestController;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StoreItemController;
use App\Models\ItemOut;
use App\Http\Controllers\PreDriveTestController;
use App\Http\Controllers\WorkProgressController;
use App\Http\Controllers\PostDriveTestController;
use App\Http\Controllers\OutsourceController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Models\Customer;

use App\Http\Controllers\RequestItemOutController;
use App\Http\Controllers\PendingRequestedItemController;
use App\Http\Controllers\SpareChangeController;
use App\Http\Controllers\ServiceReminderController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\RepairDetailController;


use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/admin/login', [AdminAuthController::class, 'login']);

// Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
//     // Revoke the user's token
//     $request->user()->tokens()->delete();
//     return response()->json(['message' => 'Logged out successfully'], 200);
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    });

    Route::post('/update-profile', [UserController::class, 'updateProfile']);

    // Dashboard & Stats
    Route::middleware('permission:manage_dashboard')->group(function () {
        Route::get('/store-items/total', [StoreItemController::class, 'getTotalItems']);
        Route::get('/total-repairs', [RepairRegistrationController::class, 'totalRepairs']);
        Route::get('/total-items-out', [StoreItemController::class, 'getTotalItemsOut']);
        Route::get('/recently-incoming', [SpareRequestController::class, 'getRecentRecords']);
        Route::post('/repairs/job/batch', [RepairRegistrationController::class, 'getBatchByJobIds']);
        Route::post('/post-drive-tests/job/batch', [PostDriveTestController::class, 'batchFetch']);
        Route::post('/job-delivery-status/batch', [PostDriveTestController::class, 'batchByJobIds']);
    });

    // Job Order & Customers
    Route::middleware('permission:manage_job_order')->group(function () {
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/list-of-customer', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::get('/search-customers', function (Request $request) {
            $query = $request->query('q');
            if (!$query) return response()->json([]);
            return response()->json(Customer::where('name', 'LIKE', "%{$query}%")->pluck('name')->toArray());
        });
        Route::get('/select-customer', [VehicleController::class, 'getCustomers']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::get('/job-orders', [VehicleController::class, 'getJobOrders']);
        Route::patch('/job-orders/{id}/priority', [VehicleController::class, 'updatePriority']); 
        Route::patch('/job-orders/{id}/status', [VehicleController::class, 'updateStatus']);
        Route::post('/repairs', [RepairRegistrationController::class, 'store']);
        Route::get('/repairs', [RepairRegistrationController::class, 'index']);
        Route::get('/repairs/{id}', [RepairRegistrationController::class, 'show']);
        Route::put('/repairs/{id}', [RepairRegistrationController::class, 'update']);
        Route::delete('/repairs/{id}', [RepairRegistrationController::class, 'destroy']);
        Route::post('/repairs/import', [RepairRegistrationController::class, 'import']);
        Route::post('/add-bolo', [BolloController::class, 'store']);
        Route::get('/bolo-list', [BolloController::class, 'index']);
        Route::post('/add-inspection', [InspectionController::class, 'store']);
        Route::get('/inspection-list', [InspectionController::class, 'index']);
        Route::post('/wheel-alignment', [WheelAlignemntController::class, 'store']);
        Route::get('/wheel-list', [WheelAlignemntController::class, 'index']);
        Route::post('/pre-drive-tests', [PreDriveTestController::class, 'store']);
        Route::get('/pre-drive-tests', [PreDriveTestController::class, 'index']);
        Route::post('/post-drive-tests', [PostDriveTestController::class, 'store']);
        Route::get('/post-drive-tests', [PostDriveTestController::class, 'index']);
        Route::post('/during-drive-tests', [DurinDriveTestController::class, 'store']);
    });

    // Work Order
    Route::middleware('permission:manage_work_order')->group(function () {
        Route::post('/work-orders', [WorkOrderController::class, 'store']);
        Route::get('/work-orders', [WorkOrderController::class, 'index']);
        Route::get('/work-orders/job-card/{job_card_no}', [WorkOrderController::class, 'showByJobCardNo']);
        Route::put('/work-orders/{id}', [WorkOrderController::class, 'update']);
        Route::delete('/work-orders/{id}', [WorkOrderController::class, 'destroy']);
        Route::post('/bulk-work-order', [WorkOrderController::class, 'BulkStore']);
        Route::post('/work-progress', [WorkProgressController::class, 'store']);
        Route::get('/work-progress/{job_card_no}', [WorkProgressController::class, 'index']);
        Route::post('/progress/store-daily/{job_card_no}', [WorkOrderController::class, 'storeDailyProgress']);
        Route::get('/work-orders/{job_card_no}/average-progress', [WorkOrderController::class, 'getAverageProgressByJobCardNo']);
    });

    // Inventory
    Route::middleware('permission:manage_inventory')->group(function () {
        Route::post('/store-items', [StoreItemController::class, 'store']);
        Route::get('/store-items', [StoreItemController::class, 'index']);
        Route::put('/store-items/bulk-update', [StoreItemController::class, 'bulkUpdate']);
        Route::get('/items', [ItemController::class, 'index']);
        Route::post('/items', [ItemController::class, 'store']);
        Route::put('/items/{item}', [ItemController::class, 'update']);
        Route::delete('/items/{item}', [ItemController::class, 'destroy']);
        Route::get('/items/history/{code}', [StoreItemController::class, 'getHistory']);
        Route::get('/items/low-stock', [ItemController::class, 'getLowStockItems']);
        Route::get('/items/out', [ItemController::class, 'getItemOutRecords']);
    });

    // Payment
    Route::middleware('permission:manage_payment')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/job/{id}', [PaymentController::class, 'show']);
        Route::put('/payments/job/{id}', [PaymentController::class, 'update']);
        Route::delete('/payments/job/{id}', [PaymentController::class, 'destroy']);
    });

    // Sales
    Route::middleware('permission:manage_sales')->group(function () {
        Route::post('/sales', [SaleController::class, 'store']);
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);
        Route::put('/sales/{id}', [SaleController::class, 'update']);
    });

    // Purchase
    Route::middleware('permission:manage_purchase')->group(function () {
        Route::post('/purchases', [PurchaseOrderController::class, 'store']);
        Route::get('/purchases', [PurchaseOrderController::class, 'index']);
        Route::get('/purchases/{reference_number}', [PurchaseOrderController::class, 'show']);
        Route::put('/purchases/{reference_number}', [PurchaseOrderController::class, 'update']);
        Route::delete('/purchases/{reference_number}', [PurchaseOrderController::class, 'destroy']);
    });

    // Proforma
    Route::middleware('permission:manage_proforma')->group(function () {
        Route::post('/proformas', [ProformaController::class, 'store']);
        Route::get('/proformas', [ProformaController::class, 'index']);
        Route::get('/proformas/{refNum}', [ProformaController::class, 'show']);
        Route::put('/proformas/{refNum}', [ProformaController::class, 'update']);
        Route::delete('/proformas/{refNum}', [ProformaController::class, 'destroy']);
    });

    // Expense
    Route::middleware('permission:manage_expense')->group(function () {
        Route::prefix('expenses')->group(function () {
            Route::get('/', [ExpenseController::class, 'index']);
            Route::post('/', [ExpenseController::class, 'store']);
            Route::get('/{id}', [ExpenseController::class, 'show']);
            Route::put('/{id}', [ExpenseController::class, 'update']);
            Route::delete('/{id}', [ExpenseController::class, 'destroy']);
        });
    });

    // Staff Management (Users, Roles, Permissions)
    Route::middleware('permission:manage_staff_management')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
        });
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/role/{roleId}', [PermissionController::class, 'getByRole']);
        Route::put('/permissions/role/{roleId}', [PermissionController::class, 'updateByRole']);
        
        Route::prefix('user-management')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']);
            Route::post('/users', [UserManagementController::class, 'store']);
            Route::put('/users/{user}', [UserManagementController::class, 'update']);
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
            Route::get('/roles', [UserManagementController::class, 'getRoles']);
            Route::post('/roles', [UserManagementController::class, 'storeRole']);
            Route::put('/roles/{role}', [UserManagementController::class, 'updateRole']);
            Route::delete('/roles/{role}', [UserManagementController::class, 'destroyRole']);
            Route::get('/permissions', [UserManagementController::class, 'getPermissions']);
        });
    });

    // Settings
    Route::middleware('permission:manage_setting')->group(function () {
        Route::get('/settings', [CompanySettingController::class, 'index']);
        Route::post('/settings', [CompanySettingController::class, 'store']);
    });

    // Check List
    Route::middleware('permission:manage_check_list')->group(function () {
        Route::get('/service-reminders', [ServiceReminderController::class, 'index']);
        Route::post('/service-reminders', [ServiceReminderController::class, 'store']);
        Route::get('/service-reminders/{id}', [ServiceReminderController::class, 'show']);
    });

    // Search (Accessible by all authenticated users for now, or specific permission if needed)
    Route::prefix('search')->group(function () {
        Route::get('/universal', [SearchController::class, 'universalSearch']);
        Route::get('/advanced', [SearchController::class, 'advancedSearch']);
    });
});
