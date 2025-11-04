<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\InventoryMovement; // [cite: 328]
use App\Http\Requests\StorePurchaseOrderRequest; // Tạo file này
use App\Http\Requests\UpdatePurchaseOrderRequest; // Tạo file này
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', PurchaseOrder::class); // Cần tạo Policy

        $query = PurchaseOrder::with('supplier', 'user')->latest(); // Eager load

        // Filter logic (status, supplier, date range - tương tự Products)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('po_code', 'like', "%{$term}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }


        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id'); // Cho bộ lọc

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        // $this->authorize('create', PurchaseOrder::class);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('purchase-orders.create', compact('suppliers'));
    }

    // public function store(StorePurchaseOrderRequest $request, NotificationService $notificationService)
    // {
    //     // $this->authorize('create', PurchaseOrder::class);
    //     $validated = $request->validated();

    //     DB::beginTransaction();
    //     try {
    //         $totalAmount = 0;
    //         foreach ($validated['items'] as $item) {
    //             $totalAmount += $item['quantity'] * $item['price'];
    //         }

    //         $purchaseOrder = PurchaseOrder::create([
    //             'supplier_id' => $validated['supplier_id'],
    //             'user_id' => Auth::id(),
    //             'order_date' => $validated['order_date'],
    //             'expected_date' => $validated['expected_date'] ?? null,
    //             'notes' => $validated['notes'] ?? null,
    //             'status' => 'pending', // Mặc định là pending
    //             'total_amount' => $totalAmount,
    //         ]);

    //         foreach ($validated['items'] as $item) {
    //             $purchaseOrder->items()->create([
    //                 'product_id' => $item['product_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'subtotal' => $item['quantity'] * $item['price'],
    //             ]);
    //         }

    //         DB::commit();

    //         // Gửi thông báo (tương tự các controller khác)
    //         $notificationService->notify(Auth::user(), 'success', 'Phiếu nhập mới', 'PO ' . $purchaseOrder->po_code . ' đã được tạo.');
    //         session()->flash('toast', ['type' => 'success', 'message' => 'Tạo phiếu nhập kho thành công!']);

    //         return redirect()->route('admin.purchase-orders.index');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Lỗi tạo PO: ' . $e->getMessage());
    //         session()->flash('toast', ['type' => 'error', 'message' => 'Tạo phiếu nhập kho thất bại.']);
    //         return redirect()->back()->withInput();
    //     }
    // }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $po = DB::transaction(function () use ($validatedData) {
            $totalAmount = collect($validatedData['items'])->sum(function($item) {
                return $item['quantity'] * $item['price'];
            });

            // Tạo mã phiếu nhập kho tự động
            $poCode = 'PO-' . now()->format('Ymd') . '-' . str_pad(PurchaseOrder::whereDate('created_at', now()->format('Y-m-d'))->count() + 1, 3, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_code' => $poCode, // Thêm mã phiếu
                'supplier_id' => $validatedData['supplier_id'],
                'user_id' => request()->user()->id,
                'order_date' => $validatedData['order_date'],
                'expected_date' => $validatedData['expected_date'],
                'notes' => $validatedData['notes'],
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            foreach ($validatedData['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return $po;
        });

        return redirect()->route('admin.purchase-orders.show', $po)
                        ->with('success', 'Tạo phiếu nhập kho thành công!');
    }
    public function show(PurchaseOrder $purchaseOrder)
    {
        // $this->authorize('view', $purchaseOrder);
        $purchaseOrder->load('supplier', 'user', 'items.product'); // Eager load chi tiết
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        // $this->authorize('update', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu nhập đang chờ xử lý.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $purchaseOrder->load('items.product'); // Load items để pre-fill form
        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('update', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể sửa phiếu nhập đang chờ xử lý.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                // Không cập nhật user_id
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            // Xóa item cũ và thêm item mới (cách đơn giản)
            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'info', 'Cập nhật phiếu nhập', 'PO ' . $purchaseOrder->po_code . ' đã được cập nhật.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Cập nhật phiếu nhập kho thành công!']);

            return redirect()->route('admin.purchase-orders.show', $purchaseOrder); // Chuyển về trang show

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật PO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Cập nhật phiếu nhập kho thất bại.']);
            return redirect()->back()->withInput();
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('delete', $purchaseOrder);
        if ($purchaseOrder->status !== 'pending' && $purchaseOrder->status !== 'cancelled') {
            session()->flash('toast', ['type' => 'warning', 'message' => 'Chỉ có thể xóa phiếu nhập đang chờ hoặc đã hủy.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        try {
            $poCode = $purchaseOrder->po_code;
            $purchaseOrder->delete(); // Soft delete

            $notificationService->notify(Auth::user(), 'warning', 'Xóa phiếu nhập', 'PO ' . $poCode . ' đã được xóa.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Xóa phiếu nhập kho thành công!']);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa PO: ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Xóa phiếu nhập kho thất bại.']);
        }
        return redirect()->route('admin.purchase-orders.index');
    }

    /**
     * Xử lý nhận hàng cho Phiếu Nhập Kho. [cite: 353]
     */
    public function receive(PurchaseOrder $purchaseOrder, NotificationService $notificationService)
    {
        // $this->authorize('approve', $purchaseOrder); // Cần quyền 'purchase_orders.approve' [cite: 239]
        if ($purchaseOrder->status !== 'pending' && $purchaseOrder->status !== 'processing') { // Cho phép nhận hàng nếu đang chờ hoặc đang xử lý
            session()->flash('toast', ['type' => 'warning', 'message' => 'Phiếu nhập này không thể nhận hàng.']);
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái PO
            $purchaseOrder->update(['status' => 'completed']);

            // Cập nhật số lượng tồn kho và tạo Inventory Movement
            foreach ($purchaseOrder->items as $item) {
                // Sử dụng lockForUpdate để tránh race condition [cite: 479]
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->quantity += $item->quantity; // Tăng số lượng [cite: 353]
                    $product->save();

                    // Tạo Inventory Movement [cite: 353]
                    InventoryMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in', // Loại 'in' [cite: 328]
                        'quantity_change' => $item->quantity, // Số lượng thay đổi
                        'quantity_after' => $product->quantity, // Số lượng sau khi thay đổi
                        'source_type' => PurchaseOrder::class, // Loại nguồn [cite: 455]
                        'source_id' => $purchaseOrder->id, // ID nguồn [cite: 456]
                        'user_id' => Auth::id(), // Người thực hiện
                        'notes' => 'Nhập hàng từ ' . $purchaseOrder->po_code, // Ghi chú [cite: 328] ('reason')
                    ]);
                } else {
                    throw new \Exception("Sản phẩm ID {$item->product_id} không tồn tại.");
                }
            }

            DB::commit();

            $notificationService->notify(Auth::user(), 'success', 'Nhận hàng thành công', 'Đã nhận hàng cho PO ' . $purchaseOrder->po_code . '.');
            session()->flash('toast', ['type' => 'success', 'message' => 'Nhận hàng thành công! Tồn kho đã được cập nhật.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi nhận hàng PO ' . $purchaseOrder->id . ': ' . $e->getMessage());
            session()->flash('toast', ['type' => 'error', 'message' => 'Nhận hàng thất bại: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
    }

    // Hàm tìm kiếm Product trả về JSON cho Alpine
    public function searchJson(Request $request)
    {
        $term = $request->input('term', '');
        if (empty($term)) {
            return response()->json([]);
        }
        $products = Product::where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        })
            ->where('status', '!=', 'maintenance') // Chỉ lấy SP đang hoạt động
            ->select('id', 'name', 'sku', 'price_buy', 'unit') // Lấy các trường cần thiết
            ->limit(10)
            ->get();
        return response()->json($products);
    }
}
