<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseOrderController;

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

    // Route chho tìm kiếm khách hàng
    Route::get('/customers/search-json', [CustomerController::class, 'searchJson'])->name('customers.search.json');

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
    // Route tùy chỉnh cho hành động "Nhận hàng"
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
});

require __DIR__.'/auth.php';
