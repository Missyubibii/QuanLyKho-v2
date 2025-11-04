<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseOrder; // Giả định bạn có model này
use App\Models\SalesOrder;    // Giả định bạn có model này
use App\Models\InventoryMovement; // Giả định bạn có model này

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

        // 2. Lấy các chỉ số đơn hàng (Giả định)
        // BẠN CẦN ĐẢM BẢO CÁC MODEL VÀ TÊN CỘT 'status' NÀY TỒN TẠI
        // $pendingPurchases = class_exists(PurchaseOrder::class) ? PurchaseOrder::where('status', 'pending')->count() : 0;
        // $pendingSales = class_exists(SalesOrder::class) ? SalesOrder::where('status', 'pending')->count() : 0;

        // 3. Lấy danh sách sản phẩm sắp hết hàng
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('quantity', 'asc')
            ->take(5) // Lấy 5 sản phẩm
            ->get();

        // 4. Lấy các hoạt động gần đây (Giả định)
        // $recentMovements = class_exists(InventoryMovement::class)
        //     ? InventoryMovement::with('product') // Tải kèm thông tin sản phẩm
        //         ->latest() // Sắp xếp mới nhất
        //         ->take(5) // Lấy 5 hoạt động
        //         ->get()
        //     : []; // Trả về mảng rỗng nếu model không tồn tại

        // 5. Trả về view với tất cả dữ liệu
        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            // 'pendingPurchases',
            // 'pendingSales',
            'lowStockProducts',
            // 'recentMovements'
        ));
    }
}
