<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $query = InventoryMovement::with('product', 'user', 'source')
            ->latest('created_at'); // Mới nhất lên trên cùng

        // Bộ lọc
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(25)->withQueryString();

        // Lấy danh sách sản phẩm để làm bộ lọc dropdown
        $products = Product::orderBy('name')->pluck('name', 'id');

        return view('inventory-movements.index', compact('movements', 'products'));
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryMovement $inventoryMovement)
    {
        $this->authorize('view', $inventoryMovement);

        // Tải các quan hệ cần thiết để hiển thị
        $inventoryMovement->load('product', 'user', 'source');

        return view('inventory-movements.show', compact('inventoryMovement'));
    }
}
