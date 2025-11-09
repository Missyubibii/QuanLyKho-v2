<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\InventoryMovement;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang tổng quan (dashboard)
     */
    public function index()
    {
        // 1. Lấy các chỉ số tổng quan
        $totalProducts = Product::count();

        $lowStockCount = Product::whereColumn('quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->count();

        // 2. Lấy các chỉ số đơn hàng
        $pendingPurchases = PurchaseOrder::where('status', 'pending')->count();
        $pendingSales = SalesOrder::where('status', 'pending')->count();

        // 3. Lấy danh sách sản phẩm sắp hết hàng
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('quantity', 'asc')
            ->take(5) // Lấy 5 sản phẩm
            ->get();

        // 4. Lấy các hoạt động gần đây (Bỏ comment VÀ SỬA TÊN BIẾN)
        $inventoryMovements = InventoryMovement::with('product') // Tải kèm thông tin sản phẩm
                ->latest() // Sắp xếp mới nhất
                ->take(5) // Lấy 5 hoạt động
                ->get();

        // 5. Trả về view với tất cả dữ liệu
        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'pendingPurchases',
            'pendingSales',
            'lowStockProducts',
            'inventoryMovements'
        ));
    }
}
