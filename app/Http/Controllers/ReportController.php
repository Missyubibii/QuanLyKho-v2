<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Báo cáo Tồn kho Hiện tại
     */
    public function inventoryReport(Request $request)
    {
        $this->authorize('view', 'reports'); // Cần tạo Policy cho Report

        $query = Product::with('supplier', 'category')
            ->orderBy('quantity', 'asc'); // Ưu tiên hàng sắp hết

        // Lọc theo trạng thái tồn kho
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'low_stock') {
                // Lọc sản phẩm sắp hết hàng
                $query->whereColumn('quantity', '<=', 'min_stock')
                    ->where('min_stock', '>', 0);
            } elseif ($request->stock_status == 'out_of_stock') {
                $query->where('quantity', '=', 0);
            }
        }

        // Lọc theo tìm kiếm
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        $products = $query->paginate(25)->withQueryString();

        return view('reports.inventory', compact('products'));
    }

    /**
     * Báo cáo Lịch sử Nhập/Xuất (Biến động kho)
     */
    public function movementReport(Request $request)
    {
        $this->authorize('view', 'reports');

        $query = InventoryMovement::with('product', 'user', 'source')
            ->latest(); // Mới nhất lên trước

        // Lọc theo ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Lọc theo loại (Nhập/Xuất) [cite: 328]
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo sản phẩm
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->paginate(25)->withQueryString();

        // Lấy danh sách sản phẩm cho bộ lọc dropdown
        $products = Product::orderBy('name')->pluck('name', 'id');

        return view('reports.movements', compact('movements', 'products'));
    }

    /**
     * Dữ liệu tóm tắt cho biểu đồ (Chart.js)
     * [cite: 276, 365, 473]
     */
    public function movementSummary(Request $request)
    {
        $this->authorize('view', 'reports');

        $days = $request->input('days', 7); // Mặc định 7 ngày

        $movementsIn = InventoryMovement::where('type', 'in')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity_change) as total')
            ])
            ->pluck('total', 'date');

        $movementsOut = InventoryMovement::where('type', 'out')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity_change) as total')
            ])
            ->pluck('total', 'date');

        // Chuẩn bị labels (các ngày)
        $labels = [];
        $dataIn = [];
        $dataOut = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            $dataIn[] = $movementsIn->get($date, 0);
            $dataOut[] = $movementsOut->get($date, 0);
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tổng Nhập',
                    'data' => $dataIn,
                    'borderColor' => '#4ade80', 
                    'backgroundColor' => '#4ade8033',
                    'fill' => true,
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Tổng Xuất',
                    'data' => $dataOut,
                    'borderColor' => '#f87171',
                    'backgroundColor' => '#f8717133',
                    'fill' => true,
                    'tension' => 0.1,
                ]
            ]
        ]);
    }
}
