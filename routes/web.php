<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\InventoryMovementController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Group các route admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Route cho tìm kiếm sản phẩm (sẽ dùng trong form create/edit)
    Route::get('/products/search-json', [ProductController::class, 'searchJson'])->name('products.search-json');
    Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');

    // Route cho tìm kiếm nhà cung cấp
    Route::get('/suppliers/search-json', [SupplierController::class, 'searchJson'])->name('suppliers.search.json');

    // Route cho tìm kiếm khách hàng
    Route::get('/customers/search-json', [CustomerController::class, 'searchJson'])->name('customers.search.json');

    // Route cho tìm kiếm phiếu nhập kho
    Route::get('/purchase-orders/search-json', [PurchaseOrderController::class, 'searchJson'])->name('purchase-orders.search.json');

    // Route cho tìm kiếm phiếu xuất kho
    Route::get('/sales-orders/search-json', [SalesOrderController::class, 'searchJson'])->name('sales-orders.search.json');

    // Resource route cho products
    Route::resource('products', ProductController::class);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/bulk-delete', [SupplierController::class, 'bulkDelete'])->name('suppliers.bulk-delete');

    Route::resource('customers', CustomerController::class);
    Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');

    // Route cho Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('/purchase-orders/bulk-delete', [PurchaseOrderController::class, 'bulkDeletePOs'])->name('purchase-orders.bulk-delete');
    // Route tùy chỉnh cho hành động "Nhận hàng"
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    // Route cho Sales Orders
    Route::resource('sales-orders', SalesOrderController::class);
    Route::post('/sales-orders/bulk-delete', [SalesOrderController::class, 'bulkDeleteSOs'])->name('sales-orders.bulk-delete');
    Route::post('sales-orders/{sales_order}/ship', [SalesOrderController::class, 'ship'])->name('sales-orders.ship');

    // Route cho Inventory Movements
    Route::resource('inventory-movements', InventoryMovementController::class)->only(['index', 'show']);

    Route::get('/reports/inventory', [App\Http\Controllers\ReportController::class, 'inventoryReport'])
        ->name('reports.inventory');

    Route::get('/reports/movements', [App\Http\Controllers\ReportController::class, 'movementReport'])
        ->name('reports.movements');

    // Route (nâng cao) để lấy dữ liệu cho chart
    Route::get('/reports/movement-summary', [App\Http\Controllers\ReportController::class, 'movementSummary'])
        ->name('reports.movement-summary');
});

require __DIR__ . '/auth.php';
